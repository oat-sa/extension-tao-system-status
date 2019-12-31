import React, { Component } from 'react'
import PropTypes from 'prop-types'
import c3 from 'c3'

import NumericCheck from 'components/NumericCheck'
import l from 'utils/intl'

import styles from 'components/Charts/charts.module.scss'

class ChartsContainer extends Component {
  componentDidMount() {
    const { redisFreeSpace, rdsFreeSpace } = this.props

    c3.generate({
      bindto: '.js-tasks-donut-1',
      data: {
        columns: [
          [l('Free space'), redisFreeSpace],
          [l('Used space'), 100 - redisFreeSpace],
        ],
        type: 'donut',
      },
    });
    c3.generate({
      bindto: '.js-tasks-donut-2',
      data: {
        columns: [
          [l('Free space'), rdsFreeSpace],
          [l('Used space'), 100 - rdsFreeSpace],
        ],
        type: 'donut',
      },
    });
  }

  render() {
    const {tasksCount} = this.props;

    return (
      <div className={styles.container}>
        <div>
          <div className={styles.title}>{l('Used space on ElastiCache storage')}</div>
          <div className="js-tasks-donut-1" style={{ width: 300 }} />
        </div>
        <div>
          <div className={styles.title}>{l('Used space on RDS storage')}</div>
          <div className="js-tasks-donut-2" style={{ width: 300 }} />
        </div>
        <NumericCheck count={tasksCount} />
      </div>
    )
  }
}

ChartsContainer.propTypes = {
  rdsFreeSpace: PropTypes.number.isRequired,
  redisFreeSpace: PropTypes.number.isRequired,
  tasksCount: PropTypes.number.isRequired,
}

export default ChartsContainer
