<?php

namespace oat\taoSystemStatus\scripts\tools;

use oat\oatbox\extension\script\ScriptAction;
use oat\tao\model\notifications\AlarmNotificationService;


/**
 * Class SetUpAlarmNotificationConfig
 *
 * Running config script:
 * ```bash
 * $ sudo -u www-data php index.php  'oat\taoSystemStatus\scripts\tools\SetUpAlarmNotificationConfig' -c '\oat\tao\model\notifiers\GrafanaNotifier::class' -p '["https://a-prod-us-central-0.grafana.net/integrations/v1/webhook/x22NqkgBRhK4kkTt3XYoqJXYR/",{"stack":"nextgen-stack"}]'
 * ```
 *
 */
class SetUpAlarmNotificationConfig extends ScriptAction
{
    protected function provideOptions()
    {
        return [
            'notifierClass' => [
                'prefix' => 'c',
                'longPrefix' => 'class',
                'required' => true,
                'description' => 'Example: \oat\tao\model\notifiers\GrafanaNotifier::class'
            ],
            'parameters' => [
                'prefix' => 'p',
                'longPrefix' => 'parameters',
                'required' => true,
                'description' => 'List the necessary parameters. They must be provided as a json string'
            ],
        ];
    }

    protected function provideDescription()
    {
        return  'The script sets notifiers in the AlarmNotificationService.conf.php';
    }

    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
        ];
    }

    protected function run()
    {
        /** @var \oat\tao\model\notifications\AlarmNotificationService $service */
        $service = $this->getServiceLocator()->get(AlarmNotificationService::SERVICE_ID);
        $array = $service->getOption('notifiers');

        $class = $this->getOption('notifierClass');
        $params = json_decode($this->getOption('parameters'), true);

        $array['class']  = $class;
        $array['params'] = $params;

        $service->setOption('notifiers', $array);
        $this->registerService(AlarmNotificationService::SERVICE_ID, $service);
    }

}
