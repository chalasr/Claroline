import React, {PropTypes as T} from 'react'
import {connect} from 'react-redux'
import selectors from './../../selectors'
import {selectors as paperSelectors} from './../selectors'
import {tex} from './../../../utils/translate'

export const PaperRow = props =>
  <tr>
    {props.admin &&
      <td>{props.user.name}</td>
    }
    <td>{props.number}</td>
    <td className="text-right">
      <small className="text-muted">{props.startDate}</small>
    </td>
    <td className="text-right">
      <small className="text-muted">{props.endDate || '-'}</small>
    </td>
    <td>{tex(props.finished ? 'yes' : 'no')}</td>
    <td className="text-right">
      {props.score || 0 === props.score ?
        <span className="label label-primary">{props.score}</span> : '-'
      }
    </td>
    <td className="text-right table-actions">
      <a href={`#papers/${props.id}`} className="btn btn-link">
        <span className="fa fa-fw fa-eye"></span>
      </a>
    </td>
  </tr>

PaperRow.propTypes = {
  admin: T.bool.isRequired,
  id: T.string.isRequired,
  user: T.shape({
    name: T.string.isRequired
  }).isRequired,
  number: T.number.isRequired,
  startDate: T.string.isRequired,
  endDate: T.string,
  finished: T.bool.isRequired,
  score: T.number
}

let Papers = props =>
  <div className="papers-list">
    <table className="table table-striped table-hover">
      <thead>
        <tr>
          {props.admin &&
            <th>{tex('paper_list_table_user')}</th>
          }
          <th>{tex('paper_list_table_paper_number')}</th>
          <th>{tex('paper_list_table_start_date')}</th>
          <th>{tex('paper_list_table_end_date')}</th>
          <th>{tex('paper_finished')}</th>
          <th>{tex('paper_list_table_score')}</th>
          <th><span className="sr-only">{tex('actions')}</span></th>
        </tr>
      </thead>
      <tbody>
        {props.papers.map((paper, idx) =>
          <PaperRow key={idx} admin={props.admin} {...paper}/>
        )}
      </tbody>
    </table>
  </div>

Papers.propTypes = {
  admin: T.bool.isRequired,
  papers: T.arrayOf(T.object).isRequired
}

function mapStateToProps(state) {
  return {
    admin: selectors.editable(state),
    papers: paperSelectors.papers(state)
  }
}

const ConnectedPapers = connect(mapStateToProps)(Papers)

export {ConnectedPapers as Papers}
