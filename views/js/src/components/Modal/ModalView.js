import React from 'react'
import PropTypes from 'prop-types'

import styles from 'components/Modal/modal.module.scss'

const ModalView = ({ children, isOpen, onClose, styles: modalStyles, title }) => {
  if (!isOpen) {
    return null
  }

  return (
    <div className={styles.container} onClick={onClose}>
      <div className={styles.modal} onClick={(e) => e.stopPropagation()} style={modalStyles}>
        <div className={styles.head}>
          <div className={styles.title}>
            {title}
          </div>
          <div className={styles.close} onClick={onClose}>&times;</div>
        </div>
        <div className={styles.content}>
          {children}
        </div>
      </div>
    </div>
  )
}


ModalView.propTypes = {
  children: PropTypes.node.isRequired,
  onClose: PropTypes.func.isRequired,
  isOpen: PropTypes.bool,
  styles: PropTypes.object,
  title: PropTypes.string,
}

ModalView.defaultProps = {
  isOpen: false,
  styles: {},
  title: '',
}

export default ModalView
