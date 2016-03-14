export default class ResultsService {
  constructor ($http, uploader) {
    this.$http = $http
    this.uploader = uploader
    this._resultId = ResultsService._getGlobal('resultId')
    this._marks = ResultsService._getGlobal('resultMarks')
    this._users = ResultsService._getGlobal('workspaceUsers')
  }

  getResults () {
    return this._marks
  }

  getUsers () {
    return this._users
  }

  createMark (props, onFail) {
    const user = this._users.find(user => user.name === props.user)
    const result = { name: props.user, mark: props.mark }
    const url = Routing.generate('claro_create_mark', {
      id: this._resultId,
      userId: user.id
    })

    this._marks.push(result);

    this.$http
      .post(url, { mark: props.mark })
      .then(
        response => result.markId = response.data,
        () => {
          this._deleteMark(result)
          onFail()
        }
      )
  }

  deleteMark (mark, onFail) {
    const url = Routing.generate('claro_delete_mark', {
      id: mark.markId
    })

    this._deleteMark(mark)

    this.$http
      .delete(url)
      .then(null, () => {
        this._marks.push(mark)
        onFail()
      })
  }

  editMark (originalMark, newValue, onFail) {
    if (originalMark.mark === newValue) {
      return
    }

    const originalValue = originalMark.mark
    const url = Routing.generate('claro_edit_mark', {
      id: originalMark.markId
    })

    originalMark.mark = newValue

    this.$http
      .put(url, { value: newValue })
      .then(null, () => {
        originalMark.mark = originalValue
        onFail()
      })
  }

  importMarks (file, onFail) {
    const url = Routing.generate('claro_import_marks', {
      id: this._resultId 
    })
    this.uploader
      .upload({ url, data: { file } })
      .then(function (resp) {
            console.log('Success ' + resp.config.data.file.name + 'uploaded. Response: ' + resp.data);
        }, function (resp) {
            console.log('Error status: ' + resp.status);
            onFail()
        }, function (evt) {
            var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
            console.log('progress: ' + progressPercentage + '% ' + evt.config.data.file.name);
        });
  }

  static _getGlobal (name) {
    if (typeof window[name] === 'undefined') {
      throw new Error(
        `Expected ${name} to be exposed in a window.${name} variable`
      )
    }

    return window[name]
  }

  _deleteMark (mark) {
    this._marks.splice(this._marks.indexOf(mark), 1)
  }
}
