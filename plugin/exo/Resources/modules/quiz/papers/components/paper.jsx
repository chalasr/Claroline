import React, {PropTypes as T} from 'react'
import {connect} from 'react-redux'
import Panel from 'react-bootstrap/lib/Panel'
import {tex} from './../../../utils/translate'
import {getDefinition, isQuestionType} from './../../../items/item-types'
import {selectors} from './../selectors'
import {Metadata as ItemMetadata} from './../../../items/components/metadata.jsx'
import {ScoreBox} from './../../../items/components/score-box.jsx'

let Paper = props =>
  <div className="paper">
    <h2 className="paper-title">
      {tex('correction')}&nbsp;{props.paper.number}
    </h2>

    {props.steps.map((step, idx) =>
      <div key={idx} className="item-paper">
        <h3 className="step-title">
          {step.title ? step.title : tex('step') + ' ' + (idx + 1)}
        </h3>

        {step.items.map(item =>
          isQuestionType(item.type) ?
            <Panel key={item.id}>
              {getAnswerScore(item.id, props.paper.answers) !== undefined && getAnswerScore(item.id, props.paper.answers) !== null &&
                <ScoreBox className="pull-right" score={getAnswerScore(item.id, props.paper.answers)} scoreMax={getItemScoreMax(item)}/>
              }
              {item.title &&
                <h4 className="item-title">{item.title}</h4>
              }

              <ItemMetadata item={item} />

              {React.createElement(
                getDefinition(item.type).paper,
                {item, answer: getAnswer(item.id, props.paper.answers), answerObject: getAnswerObject(item.id, props.paper.answers)}
              )}

              {item.feedback &&
                <hr className="item-content-separator" />
              }

              {item.feedback &&
                <div className="item-feedback" dangerouslySetInnerHTML={{__html: item.feedback}} />
              }
            </Panel> :
            ''
        )}
      </div>
    )}
  </div>

Paper.propTypes = {
  paper: T.shape({
    id: T.string.isRequired,
    number: T.number.isRequired
  }).isRequired,
  steps: T.arrayOf(T.shape({
    items: T.arrayOf(T.shape({
      id: T.string.isRequired,
      content: T.string,
      type: T.string.isRequired
    })).isRequired
  })).isRequired
}

function getAnswer(itemId, answers) {
  const answer = answers.find(answer => answer.questionId === itemId)

  return answer && answer.data ? answer.data : undefined
}

function getAnswerObject(itemId, answers) {
  return answers.find(answer => answer.questionId === itemId)
}

function getAnswerScore(itemId, answers) {
  const answer = answers.find(answer => answer.questionId === itemId)

  return answer ? answer.score : undefined
}

function getItemScoreMax(item) {
  let scoreMax

  if (item && item.score) {
    let expectedAnswers = []

    switch (item.score.type) {
      case 'manual':
        scoreMax = item.score.max
        break
      case 'fixed':
        scoreMax = item.score.success
        break
      case 'sum':
        expectedAnswers = getDefinition(item.type).expectAnswer(item)

        if (expectedAnswers.length > 0) {
          scoreMax = 0
          expectedAnswers.forEach(ca => {
            if (ca.score && ca.score > 0) {
              scoreMax += ca.score
            }
          })
        }
        break
    }
  }
  return scoreMax
}

function mapStateToProps(state) {
  return {
    paper: selectors.currentPaper(state),
    steps: selectors.paperSteps(state)
  }
}

const ConnectedPaper = connect(mapStateToProps)(Paper)

export {ConnectedPaper as Paper}
