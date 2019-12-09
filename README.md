# extension-tao-system-status

Extension supposed to be used to monitor the status of some services and the correct configuration of the TAO.


## Installation

Requires the following CRON job on ALL instances (web and workers):

```
0 * * * * root cd /var/www/html/tao && /usr/bin/flock -n /var/lock/tao-InstanceCheck.lock -c "sudo -u www-data nohup /usr/bin/php index.php 'oat\taoSystemStatus\scripts\tools\RunInstanceCheck' 2>&1 >>/var/log/tao/InstanceCheck.log &"
```

## Link to the help desk:

Configure `\oat\taoSystemStatus\model\SystemStatus\SystemStatusService::OPTION_SUPPORT_PORTAL_LINK` of `SystemStatusService` service 
with appropriate url to show the link to the Help Desk portal on the Tao's system status page 
