import { Plugin, ButtonView, Command, createDropdown, addToolbarToDropdown } from 'ckeditor5'

const DIR_BLOCKS = ['paragraph', 'heading2', 'heading3', 'heading4', 'blockQuote', 'tableCell']

function selectedDirBlocks(editor) {
  const blocks = []

  for (const block of editor.model.document.selection.getSelectedBlocks()) {
    if (editor.model.schema.checkAttribute(block, 'dir')) {
      blocks.push(block)
    }

    const cell = block.findAncestor('tableCell')
    if (cell && editor.model.schema.checkAttribute(cell, 'dir') && !blocks.includes(cell)) {
      blocks.push(cell)
    }
  }

  return blocks
}

class TextDirectionCommand extends Command {
  constructor(editor, direction) {
    super(editor)
    this.direction = direction
  }

  refresh() {
    const blocks = selectedDirBlocks(this.editor)
    this.isEnabled = blocks.length > 0
    this.value = blocks.length > 0 && blocks.every((block) => block.getAttribute('dir') === this.direction)
  }

  execute() {
    const { model } = this.editor
    const direction = this.direction

    model.change((writer) => {
      for (const block of selectedDirBlocks(this.editor)) {
        writer.setAttribute('dir', direction, block)
      }
    })
  }
}

export class TextDirection extends Plugin {
  static get pluginName() {
    return 'TextDirection'
  }

  init() {
    const editor = this.editor
    const schema = editor.model.schema

    schema.extend('$block', { allowAttributes: 'dir' })
    schema.setAttributeProperties('dir', { isFormatting: true })

    DIR_BLOCKS.forEach((name) => {
      if (schema.isRegistered(name)) {
        schema.extend(name, { allowAttributes: 'dir' })
      }
    })

    editor.conversion.attributeToAttribute({
      model: 'dir',
      view: 'dir',
    })

    editor.commands.add('ltr', new TextDirectionCommand(editor, 'ltr'))
    editor.commands.add('rtl', new TextDirectionCommand(editor, 'rtl'))

    this._addButton('ltr', 'LTR')
    this._addButton('rtl', 'RTL')

    editor.ui.componentFactory.add('textDirection', (locale) => {
      const dropdown = createDropdown(locale)
      const buttons = [
        editor.ui.componentFactory.create('ltr'),
        editor.ui.componentFactory.create('rtl'),
      ]

      addToolbarToDropdown(dropdown, buttons)
      dropdown.buttonView.set({
        label: 'Text direction',
        tooltip: true,
        withText: true,
      })
      dropdown.bind('isEnabled').toMany(buttons, 'isEnabled', (...states) => states.some(Boolean))

      return dropdown
    })
  }

  _addButton(name, label) {
    const editor = this.editor

    editor.ui.componentFactory.add(name, (locale) => {
      const command = editor.commands.get(name)
      const button = new ButtonView(locale)

      button.set({
        label,
        tooltip: true,
        withText: true,
        isToggleable: true,
      })
      button.bind('isOn').to(command, 'value')
      button.bind('isEnabled').to(command, 'isEnabled')
      button.on('execute', () => {
        editor.execute(name)
        editor.editing.view.focus()
      })

      return button
    })
  }
}

export class IndentDirection extends Plugin {
  static get pluginName() {
    return 'IndentDirection'
  }

  static get requires() {
    return ['IndentBlock']
  }

  afterInit() {
    const editor = this.editor

    editor.conversion.for('upcast').attributeToAttribute({
      view: { styles: { 'margin-left': /[\s\S]+/ } },
      model: {
        key: 'blockIndent',
        value: (viewElement) => {
          if (!viewElement.is('element', 'li')) {
            return viewElement.getStyle('margin-left')
          }
        },
      },
    })

    editor.conversion.for('upcast').attributeToAttribute({
      view: { styles: { 'margin-right': /[\s\S]+/ } },
      model: {
        key: 'blockIndent',
        value: (viewElement) => {
          if (!viewElement.is('element', 'li')) {
            return viewElement.getStyle('margin-right')
          }
        },
      },
    })

    editor.conversion.for('downcast').add((dispatcher) => {
      dispatcher.on('attribute:blockIndent', (evt, data, conversionApi) => {
        if (!conversionApi.consumable.consume(data.item, evt.name)) {
          return
        }

        const viewElement = conversionApi.mapper.toViewElement(data.item)
        if (!viewElement) {
          return
        }

        const dir = data.item.getAttribute('dir') || editor.locale.contentLanguageDirection
        const marginProperty = dir === 'rtl' ? 'margin-right' : 'margin-left'
        const oppositeProperty = dir === 'rtl' ? 'margin-left' : 'margin-right'

        conversionApi.writer.removeStyle(oppositeProperty, viewElement)

        if (data.attributeNewValue) {
          conversionApi.writer.setStyle(marginProperty, data.attributeNewValue, viewElement)
        } else {
          conversionApi.writer.removeStyle(marginProperty, viewElement)
        }
      }, { priority: 'high' })
    })
  }
}
