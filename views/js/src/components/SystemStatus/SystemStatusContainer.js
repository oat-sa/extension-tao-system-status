import React, { Component, Fragment } from 'react'

import { getReports } from 'api/reportsService'
import { reportsSelector } from 'selectors/reportsSelectors'
import Modal from 'components/Modal'
import ReportTable from 'components/ReportTable'
import SystemStatusView from 'components/SystemStatus/SystemStatusView'
import l from 'utils/intl'

class SystemStatusContainer extends Component {
  state = {
    reports: undefined,
    showModal: false,
  }

  componentDidMount() {
    getReports()
      .then((data) => {
        this.setState({
          reports: reportsSelector(data),
        })
      })
      .catch((error) => console.log(error))
  }

  closeModal = () => {
    this.setState({
      reportDetails: undefined,
      showModal: false,
    })
  }

  showReportDetails = (reportDetails) => {
    this.setState({
      reportDetails,
      showModal: true,
    });
  }

  render() {
    const { reports, reportDetails, showModal } = this.state

    if (!reports) {
      return null
    }

    return (
      <Fragment>
        <SystemStatusView
          onShowDetails={this.showReportDetails}
          reports={reports}
        />
        {reportDetails && (
          <Modal onClose={this.closeModal} isOpen={showModal}>
            <ReportTable
              category={l('Task details')}
              columns={[l('Status'), l('Description')]}
              rows={
                reportDetails
                  .map(({ type, message }) => ({
                    type,
                    [`is${type}`]: true,
                    cells: [message.replace(/\r?\n/g, '<br />')],
                  }))
              }
            />
          </Modal>
        )}
      </Fragment>
    )
  }
}

export default SystemStatusContainer
