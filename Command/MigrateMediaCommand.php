<?php

namespace Kunstmaan\MediaBundle\Command;

use Doctrine\ORM\EntityManager;
use Kunstmaan\MediaBundle\Repository\FolderRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateMediaCommand extends ContainerAwareCommand
{
    /** @var EntityManager $em */
    protected $em;

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('kuma:media:migrate')
            ->setDescription('Migrate media to new table structure.')
            ->setHelp(
                "The <info>kuma:media:migrate</info> command can be used to migrate media to the new table structure."
            );
    }

    private function migrateMedia(OutputInterface $output)
    {
        $output->writeln('Migrating media...');
        $medias  = $this->em->getRepository('KunstmaanMediaBundle:Media')->findBy(
            array('location' => 'local', 'deleted' => false)
        );
        $updates = 0;
        try {
            $this->em->beginTransaction();
            /** @var Media $media */
            foreach ($medias as $media) {
                $filename = $media->getOriginalFilename();
                if (empty($filename)) {
                    $media->setOriginalFilename($media->getName());
                    $this->em->persist($media);
                    $updates++;
                }
            }
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            $output->writeln('An error occured while migrating media : <error>' . $e->getMessage() . '</error>');
        }
        $output->writeln('<info>' . $updates . ' media files have been migrated.</info>');
    }

    private function migrateFolders(OutputInterface $output)
    {
        $output->writeln('Migrating media folders...');

        $sql = "DROP PROCEDURE IF EXISTS createmediatree;
        SET SQL_SAFE_UPDATES=0;
        CREATE PROCEDURE `createmediatree`()
        MODIFIES SQL DATA
        BEGIN
            DECLARE currentId, currentParentId  CHAR(36);
            DECLARE currentLeft, currentLevel   INT;
            DECLARE startId                     INT DEFAULT 1;

            # Determines the max size for MEMORY tables.
            SET max_heap_table_size = 1024 * 1024 * 512;

            START TRANSACTION;

            DROP TABLE IF EXISTS `tmp_tree`;

            # Temporary MEMORY table to do all the heavy lifting in,
            # otherwise performance is simply abysmal.
            CREATE TABLE `tmp_tree` (
                `id`        char(36) NOT NULL DEFAULT '',
                `parent_id` char(36)          DEFAULT NULL,
                `lft`       int(11)  unsigned DEFAULT NULL,
                `level`     int(11)  unsigned DEFAULT NULL,
                `rgt`       int(11)  unsigned DEFAULT NULL,
                PRIMARY KEY      (`id`),
                INDEX USING HASH (`parent_id`),
                INDEX USING HASH (`lft`),
                INDEX USING HASH (`rgt`)
            ) ENGINE = MEMORY
            SELECT `id`,
                   `parent_id`,
                   `lft`,
                   `level`,
                   `rgt`
            FROM   `kuma_folders`;

            # Leveling the playing field.
            UPDATE  `tmp_tree`
            SET     `lft`   = NULL,
                    `rgt`   = NULL,
                    `level` = NULL;

            # Establishing starting numbers for all root elements.
            WHILE EXISTS (SELECT * FROM `tmp_tree` WHERE `parent_id` IS NULL AND `lft` IS NULL AND `rgt` IS NULL LIMIT 1) DO

                UPDATE `tmp_tree`
                SET    `lft`   = startId,
                       `rgt`   = startId + 1,
                       `level` = 0
                WHERE  `parent_id` IS NULL
                  AND  `lft`       IS NULL
                  AND  `rgt`       IS NULL
                LIMIT  1;

                SET startId = startId + 2;

            END WHILE;

            # Switching the indexes for the lft/rgt columns to B-Trees to speed up the next section, which uses range queries.
            DROP INDEX `lft`  ON `tmp_tree`;
            DROP INDEX `rgt` ON `tmp_tree`;
            CREATE INDEX `lft`  USING BTREE ON `tmp_tree` (`lft`);
            CREATE INDEX `rgt` USING BTREE ON `tmp_tree` (`rgt`);

            # Numbering all child elements
            WHILE EXISTS (SELECT * FROM `tmp_tree` WHERE `lft` IS NULL LIMIT 1) DO

                # Picking an unprocessed element which has a processed parent.
                SELECT     `tmp_tree`.`id`, `parents`.`level`
                  INTO     currentId, currentLevel
                FROM       `tmp_tree`
                INNER JOIN `tmp_tree` AS `parents`
                        ON `tmp_tree`.`parent_id` = `parents`.`id`
                WHERE      `tmp_tree`.`lft` IS NULL
                  AND      `parents`.`lft`  IS NOT NULL
                LIMIT      1;

                # Finding the element's parent.
                SELECT  `parent_id`
                  INTO  currentParentId
                FROM    `tmp_tree`
                WHERE   `id` = currentId;

                # Finding the parent's lft value.
                SELECT  `lft`
                  INTO  currentLeft
                FROM    `tmp_tree`
                WHERE   `id` = currentParentId;

                # Shifting all elements to the right of the current element 2 to the right.
                UPDATE `tmp_tree`
                SET    `rgt` = `rgt` + 2
                WHERE  `rgt` > currentLeft;

                UPDATE `tmp_tree`
                SET    `lft` = `lft` + 2
                WHERE  `lft` > currentLeft;

                # Setting lft and rgt values for current element.
                UPDATE `tmp_tree`
                SET    `lft`   = currentLeft + 1,
                       `rgt`   = currentLeft + 2,
                       `level` = currentLevel + 1
                WHERE  `id`    = currentId;

            END WHILE;

            # Writing calculated values back to physical table.
            UPDATE `kuma_folders`, `tmp_tree`
            SET    `kuma_folders`.`lft`   = `tmp_tree`.`lft`,
                   `kuma_folders`.`rgt`   = `tmp_tree`.`rgt`,
                   `kuma_folders`.`level` = `tmp_tree`.`level`
            WHERE  `kuma_folders`.`id`    = `tmp_tree`.`id`;

            COMMIT;

            DROP TABLE `tmp_tree`;

        END;
        CALL createmediatree();
        DROP PROCEDURE IF EXISTS createmediatree;";

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();

        $output->writeln('<info>The media folders have been migrated.</info>');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->migrateMedia($output);
        $this->migrateFolders($output);
    }
}