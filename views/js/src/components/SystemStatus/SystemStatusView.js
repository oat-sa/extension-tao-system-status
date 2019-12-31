import React from 'react'
import PropTypes from 'prop-types'

import Charts from 'components/Charts'
import ReportTable from 'components/ReportTable'
import TaskQueueStatistic from 'components/TaskQueueStatistic'
import l from 'utils/intl'

import styles from 'components/SystemStatus/systemStatus.module.scss'

const SystemStatusView = ({
  onShowDetails,
  reports: {
    configuration,
    configurationValues,
    healthCheck,
    taskQueueFails,
    taskQueueFinished,
    redisFreeSpace,
    rdsFreeSpace,
    taskQueueMonitoring,
  },
}) => (
    <div className={styles.container}>
      <h1 className={styles.header}>{l('Welcome to TAO\'s status page hub')}</h1>
      <div className={styles.configurationReportsContainer}>
        {!!configuration.length && (
          <ReportTable
            category={l('TAO Configuration')}
            columns={[l('Status'), l('Description'), l('Date')]}
            rows={
              configuration
                .map(({ type, data: { details, date } }) => ({
                  type,
                  cells: [details, new Date(date * 1000).toLocaleString()],
                }))
            }
          />
        )}
        {!!configurationValues.length && (
          <ReportTable
            category={l('Configuration Values')}
            columns={[l('Status'), l('Description'), l('Value')]}
            rows={
              configurationValues
                .map(({ type, message, data: { details } }) => ({
                  type,
                  cells: [details, message.replace(/\r?\n/g, '<br />')],
                }))
            }
          />
        )}
      </div>
      {!!healthCheck.length && (
        <ReportTable
          category={l('Health/Readiness check')}
          columns={[l('Status'), l('Description'), l('Details')]}
          rows={
            healthCheck
              .map(({ type, message, data: { details } }) => ({
                type,
                cells: [details, message.replace(/\r?\n/g, '<br />')],
              }))
          }
        />
      )}
      {taskQueueFails[0] && (
        <ReportTable
          category={l('Last failed tasks in the task queue')}
          columns={[l('Task'), l('Date'), '']}
          onShowDetails={onShowDetails}
          rows={
            taskQueueFails[0].children
              .map(({ children, data: { task_label, task_report_time } }) => ({
                cells: [task_label, task_report_time],
                detailsButton: true,
                reportData: children,
              }))
          }
        />
      )}
      {taskQueueFinished[0] && (
        <TaskQueueStatistic
          data={{
            P1D: taskQueueFinished[0].data.P1D,
            P1W: taskQueueFinished[0].data.P1W,
            P1M: taskQueueFinished[0].data.P1M,
          }}
        />
      )}
      {redisFreeSpace[0] && rdsFreeSpace[0] && taskQueueMonitoring[0] && (
        <Charts
          redisFreeSpace={redisFreeSpace[0].data.value}
          rdsFreeSpace={rdsFreeSpace[0].data.value}
          tasksCount={taskQueueMonitoring[0].data.report_value}
        />
      )}
    </div>
  )

SystemStatusView.propTypes = {
  onShowDetails: PropTypes.func.isRequired,
  reports: PropTypes.object,
}


export default SystemStatusView
