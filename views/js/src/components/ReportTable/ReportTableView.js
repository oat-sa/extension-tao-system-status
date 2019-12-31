import React from 'react'
import PropTypes from 'prop-types'
import cn from 'classnames'

import l from 'utils/intl'

import styles from 'components/ReportTable/reportTable.module.scss'

const ReportTableView = ({ category, columns, onShowDetails, rows }) => {
  return (
    <div className={styles.wrapper}>
      <div className={styles.title}>
        {category}
      </div>
      <div className={styles.container}>
        <table className={styles.table}>
          <thead>
            <tr className={`${styles.row} ${styles.headRow}`}>
              {columns.map((column, i) => (<th key={i}>{column}</th>))}
            </tr>
          </thead>
          <tbody>
            {rows.map((
              {
                cells,
                detailsButton,
                reportData,
                type
              },
              i
            ) => (
                <tr className={styles.row} key={i}>
                  {type && (
                    <td className={styles.statusCell}>
                      <div
                        className={cn({
                          [styles.iconSuccess]: type === 'success' || type === 'info',
                          [styles.iconError]: type === 'error',
                          [styles.iconWarning]: type === 'warning',
                        })}
                      >
                        <i
                          className={cn({
                            'icon-result-ok': type === 'success' || type === 'info',
                            'icon-result-nok': type === 'error',
                            'icon-warning': type === 'warning',
                          })}
                        />
                      </div>
                    </td>
                  )}
                  {cells.map((cell, i) => (
                    <td
                      key={i}
                      dangerouslySetInnerHTML={{ __html: cell }}
                    />
                  ))}
                  {detailsButton && (
                    <th>
                      <button
                        className={styles.detailsButton}
                        onClick={() => onShowDetails(reportData)}
                        type="button"
                      >
                        {l('View Report')}
                      </button>
                    </th>
                  )}
                </tr>
              ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}


ReportTableView.propTypes = {
  category: PropTypes.string.isRequired,
  columns: PropTypes.array.isRequired,
  onShowDetails: PropTypes.func,
  rows: PropTypes.array.isRequired,
}


export default ReportTableView
