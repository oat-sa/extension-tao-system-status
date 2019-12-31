const reportCategories = {
  configuration: [
      'oat\\taoSystemStatus\\model\\Check\\System\\FrontEndLogCheck',
      'oat\\taoSystemStatus\\model\\Check\\System\\TaoLtiKVCheck',
      'oat\\taoSystemStatus\\model\\Check\\System\\TaoLtiDeliveryKVCheck',
      'oat\\taoSystemStatus\\model\\Check\\System\\LockServiceCheck',
      'oat\\taoSystemStatus\\model\\Check\\System\\LocalNamespaceCheck',
      'oat\\taoSystemStatus\\model\\Check\\System\\Act\\SNSCheck',
      'oat\\taoSystemStatus\\model\\Check\\System\\Act\\OdsConfigurationCheck',
      'oat\\taoSystemStatus\\model\\Check\\System\\WebSourceTTLCheck',
  ],
  configurationValues: [
      'oat\\taoSystemStatus\\model\\Check\\System\\DefaultLanguageCheck',
      'oat\\taoSystemStatus\\model\\Check\\System\\DefaultTimeZoneCheck',
      'oat\\taoSystemStatus\\model\\Check\\System\\DebugModeCheck',
      'oat\\taoSystemStatus\\model\\Check\\System\\HeartBeatCheck',
      'oat\\taoSystemStatus\\model\\Check\\System\\AutoSystemTerminationCheck',
      'oat\\taoSystemStatus\\model\\Check\\System\\LoginQueueCheck',
  ],
  healthCheck: [
      'oat\\taoSystemStatus\\model\\Check\\System\\TaoUpdateCheck',
      'oat\\taoSystemStatus\\model\\Check\\Instance\\CronCheck',
      'oat\\taoSystemStatus\\model\\Check\\Instance\\WriteConfigDataCheck',
      'oat\\taoSystemStatus\\model\\Check\\Instance\\WkhtmltopdfCheck',
      'oat\\taoSystemStatus\\model\\Check\\Instance\\MessagesJsonCheck',
      'oat\\taoSystemStatus\\model\\Check\\Instance\\MathJaxCheck',
  ],
  taskQueueFails: ['oat\\taoSystemStatus\\model\\Check\\System\\TaskQueueFailsCheck'],
  taskQueueFinished: ['oat\\taoSystemStatus\\model\\Check\\System\\TaskQueueFinishedCheck'],
  taskQueueMonitoring: ['oat\\taoSystemStatus\\model\\Check\\System\\TaskQueueMonitoring'],
  redisFreeSpace: ['oat\\taoSystemStatus\\model\\Check\\System\\AwsRedisFreeSpaceCheck'],
  rdsFreeSpace: ['oat\\taoSystemStatus\\model\\Check\\System\\AwsRDSFreeSpaceCheck'],
}

export const reportsSelector = (({ report: { children: reports } }) => {
  const categories = Object.keys(reportCategories)

  return reports.reduce(
      (agg, item) => {
          const { data: { check_id: id } } = item
          const category = categories.find((category) =>
              reportCategories[category].indexOf(id) !== -1
          )

          if (category) {
              agg[category].push(item)
          }

          return agg
      },
      {
          configuration: [],
          configurationValues: [],
          healthCheck: [],
          taskQueueFails: [],
          taskQueueFinished: [],
          taskQueueMonitoring: [],
          redisFreeSpace: [],
          rdsFreeSpace: [],
      }
  )
})
