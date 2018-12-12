import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/competency/modals/scales/store'
import {Scale as ScaleType} from '#/plugin/competency/administration/competency/prop-types'
import {ScaleList} from '#/plugin/competency/administration/competency/scale/components/scale-list'

const ScalesPickerModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'confirmText', 'selected', 'selectAction', 'resetSelect')}
      className="scales-picker-modal"
      icon="fa fa-fw fa-arrow-up"
      bsSize="lg"
      onExiting={() => props.resetSelect()}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: ['apiv2_competency_scale_list'],
          autoload: true
        }}
        definition={ScaleList.definition}
        card={ScaleList.card}
        display={props.display}
      />

      <Button
        label={props.confirmText}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

ScalesPickerModal.propTypes = {
  title: T.string,
  confirmText: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(ScaleType.propTypes)).isRequired,
  resetSelect: T.func.isRequired
}

ScalesPickerModal.defaultProps = {
  title: trans('scales.picker', {}, 'competency'),
  confirmText: trans('select', {}, 'actions')
}

export {
  ScalesPickerModal
}