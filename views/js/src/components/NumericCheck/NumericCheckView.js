import React from 'react'
import PropTypes from 'prop-types'

import l from 'utils/intl'

import styles from 'components/NumericCheck/numericCheck.module.scss'

const NumericCheckView = ({ count }) => (
  <div className={styles.container}>
    <div className={styles.icon}>
      <i className="icon-speed" />
    </div>
    <div className={styles.description}>
      <div className={styles.descriptionTitle}>{l('Tasks In the Queue')}</div>
      <div className={styles.descriptionAmount}>{count}</div>
    </div>
  </div>
)

NumericCheckView.propTypes = {
  count: PropTypes.number.isRequired,
}

export default NumericCheckView
