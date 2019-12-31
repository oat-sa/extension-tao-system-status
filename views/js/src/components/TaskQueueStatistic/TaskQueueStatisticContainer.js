import React, { Component } from 'react'
import PropTypes from 'prop-types'
import c3 from 'c3'

import l from 'utils/intl'

import styles from 'components/TaskQueueStatistic/taskQueueStatistic.module.scss'

const chartConfig = {
  bindto: '.js-tasks-graph',
  padding: {
    bottom: 0,
    left: 0
  },
  data: {
    x: 'time',
    xFormat: '%Y-%m-%d %H:%M:%S',
    mimeType: 'json',
    type: 'line',
    names: {
      amount: l('Tasks processed'),
      average: l('Average processing time, s')
    }
  },
  tooltip: {
    format: {
      title: function (x) {
        return new Date(Date.parse(x)).toUTCString();
      }
    }
  },
  axis: {
    x: {
      type: 'timeseries',
      tick: {
        format: '%H:%M'
      },
      label: {
        position: 'bottom center'
      }
    },
    y: {
      inner: true,
      label: {
        position: 'outer-top',
      }
    }
  }
}

class TaskQueueStatisticContainer extends Component {
  state = {
    interval: 'P1D',
  }

  componentDidMount() {
    const { data } = this.props
    const { interval } = this.state

    this.chart = c3.generate({
      ...chartConfig,
      data: {
        ...chartConfig.data,
        json: data[interval],
      },
      axis: {
        ...chartConfig.axis,
        x: {
          type: 'timeseries',
          tick: {
            format: interval === 'P1D' ? '%H:%M' : '%m-%d'
          },
          label: {
            text: interval === 'P1D' ? l('Hours') : l('Days')
          }
        }
      }
    })
  }

  changeInterval = ({ target: { value } }) => {
    const { data } = this.props

    this.setState({
      interval: value,
    })

    const newConfig = {
      ...chartConfig,
      data: {
        ...chartConfig.data,
        json: data[value],
      },
      axis: {
        ...chartConfig.axis,
        x: {
          type: 'timeseries',
          tick: {
            format: value === 'P1D' ? '%H:%M' : '%m-%d'
          },
          label: {
            text: value === 'P1D' ? l('Hours') : l('Days')
          }
        }
      }
    }

    this.chart.internal.config.axis_x_tick_format = newConfig.axis.x.tick.format;
    this.chart.axis.labels({
      x: newConfig.axis.x.label.text
    });
    this.chart.load(newConfig.data);
  }

  render() {
    const { interval } = this.state

    return (
      <div className={styles.container}>
        <div className={styles.title}>
          {l('Task Queue Statistics')}
        </div>
        <div className={styles.selectContainer}>
          <select value={interval} onChange={this.changeInterval}>
            <option value="P1D">{l('Last Day')}</option>
            <option value="P1W">{l('Last Week')}</option>
            <option value="P1M">{l('Last Month')}</option>
          </select>
        </div>
        <div className="js-tasks-graph" />
      </div>
    )
  }
}

TaskQueueStatisticContainer.propTypes = {
  data: PropTypes.object.isRequired,
}

export default TaskQueueStatisticContainer
