<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 17/10/17
 * Time: 14:10.
 */

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Library\Logger\ConsoleLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanResourceTypeDatabaseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:clean:resource-type')
            ->setDescription('Remove unused resource types');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = ConsoleLogger::get($output);

        $types = [
          'claroline_survey',
          'activity',
          'innova_audio_recorder',
          'innova_video_recorder',
          'innova_media_resource',
        ];
        $this->removeResources($types, $consoleLogger);
        $this->removeTables();
    }

    public function removeResources(array $types, $consoleLogger)
    {
        $databaseManager = $this->getContainer()->get('claroline.manager.database_manager');
        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $databaseManager->setLogger($consoleLogger);

        foreach ($types as $type) {
            $batch = uniqid();
            $consoleLogger->info('Backup old nodes for type '.$type);
            $databaseManager->backupRows(ResourceNode::class, ['resourceType' => $type], 'claro_node_'.$type, $batch);
            $databaseManager->backupRows(ResourceRights::class, ['resourceType' => $type], 'claro_rights_'.$type, $batch);
            $typeEntity = $om->getRepository(ResourceType::class)->findOneByName($type);
            $nodes = $om->getRepository(ResourceNode::class)->findBy(['resourceType' => $typeEntity]);
            $manager = $this->getContainer()->get('claroline.manager.resource_manager');
            $total = count($nodes);
            $consoleLogger->info('Ready to remove '.$total.' '.$type);
            $i = 0;

            foreach ($nodes as $node) {
                ++$i;
                $consoleLogger->info('Removing '.$type.' '.$i.'/'.$total);
                $manager->delete($node, true);
            }

            $entity = $om->getRepository(ResourceType::class)->findOneByName($type);

            if ($entity) {
                $consoleLogger->info('Backup old resourcreType '.$type);
                $databaseManager->backupRows(ResourceType::class, ['name' => $type], 'claro_resource_type_'.$type, $batch);
                $consoleLogger->info('Removing type '.$type);
                $om->remove($entity);
                $om->flush();
            }

            $om->flush();
        }
    }

    public function removeTables()
    {
        $databaseManager = $this->getContainer()->get('claroline.manager.database_manager');

        $tables = [
          //surveys
          'claro_survey_multiple_choice_question_answer',
          'claro_survey_open_ended_question_answer',
          'claro_survey_question_answer',
          'claro_survey_simple_text_question_answer',
          'claro_survey_answer',
          'claro_survey_choice',
          'claro_survey_multiple_choice_question',
          'claro_survey_question',
          'claro_survey_question_model',
          'claro_survey_resource',
          'claro_survey_question_relation',
          //activities
          'claro_activity_parameters',
          'claro_activity_rule',
          'claro_activity_rule_action',
          'claro_activity_evaluation',
          'claro_activity_past_evaluation',
          //audio recorder
          'innova_audio_recorder_configuration',
          //video_recorder
          'innova_video_recorder_configuration',
          //media ressources
          'media_resource_region_config',
          'media_resource_region',
          'media_resource_options',
          'media_resource',
          'media_resource_media',
          'media_resource_help_text',
          'media_resource_help_link',
      ];

        $databaseManager->dropTables($tables, true);
    }
}