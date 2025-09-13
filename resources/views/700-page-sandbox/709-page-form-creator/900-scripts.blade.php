<script>
$(document).ready(function() {
    // Global form data
    let formData = {
        name: '',
        description: '',
        components: [],
        settings: {}
    };
    
    let selectedComponent = null;
    let componentCounter = 0;
    
    // Initialize drag and drop
    initializeDragDrop();
    
    // Initialize event handlers
    initializeEventHandlers();
    
    // Load saved forms list
    loadFormsList();
    
    /**
     * Initialize drag and drop functionality
     */
    function initializeDragDrop() {
        // Make components draggable
        $('.component-item').draggable({
            helper: 'clone',
            revert: 'invalid',
            zIndex: 1000,
            start: function(event, ui) {
                $(ui.helper).addClass('dragging');
            }
        });
        
        // Make form canvas droppable
        $('#form-canvas').droppable({
            accept: '.component-item',
            drop: function(event, ui) {
                const componentType = ui.draggable.data('component-type');
                addComponentToCanvas(componentType);
                hideEmptyState();
            },
            over: function(event, ui) {
                $(this).addClass('drag-over');
            },
            out: function(event, ui) {
                $(this).removeClass('drag-over');
            }
        });
        
        // Make form components sortable with enhanced settings
        $('#form-components').sortable({
            handle: '.component-wrapper',
            placeholder: 'sortable-placeholder bg-blue-100 border-2 border-dashed border-blue-300 rounded-md mb-4',
            tolerance: 'pointer',
            axis: 'y',
            cursor: 'move',
            opacity: 0.8,
            revert: 100,
            start: function(event, ui) {
                ui.item.addClass('sorting-active');
                $('.component-wrapper').not(ui.item).addClass('sorting-inactive');
            },
            stop: function(event, ui) {
                ui.item.removeClass('sorting-active');
                $('.component-wrapper').removeClass('sorting-inactive');
            },
            update: function(event, ui) {
                updateComponentOrder();
                showNotification('컴포넌트 순서가 변경되었습니다', 'success');
            }
        });
    }
    
    /**
     * Initialize event handlers
     */
    function initializeEventHandlers() {
        // Right panel tab switching
        $('.right-panel-tab').click(function() {
            const tab = $(this).data('tab');
            switchRightPanelTab(tab);
        });
        
        // Property tab switching (within components tab)
        $('.property-tab').click(function() {
            const tab = $(this).data('tab');
            switchPropertyTab(tab);
        });
        
        // Component selection
        $(document).on('click', '.component-wrapper', function() {
            selectComponent($(this));
        });
        
        // Property changes
        $('#component-properties input, #component-properties select, #component-properties textarea').change(function() {
            updateSelectedComponent();
        });
        
        // Apply properties
        $('#apply-properties').click(function() {
            applyComponentProperties();
        });
        
        // Reset properties
        $('#reset-properties').click(function() {
            resetComponentProperties();
        });
        
        // Form actions
        $('#btn-save').click(saveForm);
        $('#btn-export').click(exportForm);
        $('#btn-clear').click(clearForm);
        
        // Header actions
        $('#btn-form-manager').click(showFormManager);
        $('#btn-import').click(importForm);
        $('#btn-export-json').click(exportForm);
        $('#btn-save-form').click(showSaveModal);
        
        // Form manager modal
        $('#close-form-manager, #btn-close-manager').click(hideFormManager);
        $('#btn-new-form').click(newForm);
        $('#btn-refresh-forms').click(loadFormsList);
        
        // Save modal
        $('#close-save-modal, #btn-save-cancel').click(hideSaveModal);
        $('#btn-save-confirm').click(saveFormWithName);
        
        // Import file
        $('#import-file-input').change(handleImportFile);
        $('#btn-import-form').click(function() {
            $('#import-file-input').click();
        });
        
        // Search forms
        $('#search-forms').on('input', filterForms);
        
        // Right panel quick actions
        $('#quick-search-forms').on('input', quickFilterForms);
        $('#refresh-forms-list').click(loadQuickFormsList);
        $('#quick-new-form').click(newForm);
        $('#quick-import-form').click(importForm);
        $('#quick-export-current').click(exportForm);
        
        // Tree actions
        $('#expand-all-tree').click(expandAllTreeNodes);
        $(document).on('click', '.tree-node-toggle', toggleTreeNode);
        $(document).on('click', '.tree-item', selectTreeItem);
        
        // Settings actions
        $('#apply-settings').click(applyFormSettings);
        $('#reset-settings').click(resetFormSettings);
        
        // Form settings change handlers
        $('#settings-form-title, #settings-form-description, #settings-submit-text, #settings-success-message').on('input', updateFormSettings);
        $('#settings-layout-style, #settings-theme, #settings-error-position').on('change', updateFormSettings);
        $('#settings-show-progress, #settings-show-labels, #settings-required-asterisk, #settings-validate-realtime, #settings-show-errors').on('change', updateFormSettings);
        
        // Form component actions
        $(document).on('click', '.btn-load-form', function() {
            const filename = $(this).closest('.form-item').data('filename');
            loadForm(filename);
        });
        
        $(document).on('click', '.btn-delete-form', function() {
            const filename = $(this).closest('.form-item').data('filename');
            deleteForm(filename);
        });
        
        $(document).on('click', '.btn-export-form', function() {
            const filename = $(this).closest('.form-item').data('filename');
            exportSavedForm(filename);
        });
    }
    
    /**
     * Add component to canvas
     */
    function addComponentToCanvas(componentType) {
        const componentId = 'comp_' + (++componentCounter);
        let componentHtml = '';
        
        // Generate component based on type
        switch(componentType) {
            case 'input':
                componentHtml = createInputComponent(componentId);
                break;
            case 'button':
                componentHtml = createButtonComponent(componentId);
                break;
            case 'header':
                componentHtml = createHeaderComponent(componentId);
                break;
            case 'textarea':
                componentHtml = createTextareaComponent(componentId);
                break;
            case 'dropdown':
                componentHtml = createDropdownComponent(componentId);
                break;
            case 'checkbox':
                componentHtml = createCheckboxComponent(componentId);
                break;
            case 'radiogroup':
                componentHtml = createRadioGroupComponent(componentId);
                break;
            default:
                componentHtml = createGenericComponent(componentId, componentType);
        }
        
        $('#form-components').append(componentHtml);
        updateComponentCount();
        
        // Add to form data
        formData.components.push({
            id: componentId,
            type: componentType,
            properties: getDefaultProperties(componentType)
        });
        
        // Auto-select the new component
        selectComponent($('#' + componentId));
    }
    
    /**
     * Create input component HTML
     */
    function createInputComponent(componentId) {
        return `
            <div id="${componentId}" class="component-wrapper mb-4" data-type="input">
                <div class="bg-white border border-gray-200 rounded-md p-4 hover:border-blue-400 transition-colors relative group">
                    <div class="component-toolbar absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="w-6 h-6 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 mr-1" onclick="editComponent('${componentId}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="w-6 h-6 bg-red-500 text-white rounded text-xs hover:bg-red-600" onclick="deleteComponent('${componentId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <label class="component-label block text-sm font-medium text-gray-700 mb-2">텍스트 입력</label>
                    <input type="text" class="component-input w-full border border-gray-300 rounded-md px-3 py-2 text-sm" placeholder="텍스트를 입력하세요...">
                </div>
            </div>
        `;
    }
    
    /**
     * Create button component HTML
     */
    function createButtonComponent(componentId) {
        return `
            <div id="${componentId}" class="component-wrapper mb-4" data-type="button">
                <div class="bg-white border border-gray-200 rounded-md p-4 hover:border-blue-400 transition-colors relative group">
                    <div class="component-toolbar absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="w-6 h-6 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 mr-1" onclick="editComponent('${componentId}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="w-6 h-6 bg-red-500 text-white rounded text-xs hover:bg-red-600" onclick="deleteComponent('${componentId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <button class="component-button bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors">
                        버튼
                    </button>
                </div>
            </div>
        `;
    }
    
    /**
     * Create header component HTML
     */
    function createHeaderComponent(componentId) {
        return `
            <div id="${componentId}" class="component-wrapper mb-4" data-type="header">
                <div class="bg-white border border-gray-200 rounded-md p-4 hover:border-blue-400 transition-colors relative group">
                    <div class="component-toolbar absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="w-6 h-6 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 mr-1" onclick="editComponent('${componentId}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="w-6 h-6 bg-red-500 text-white rounded text-xs hover:bg-red-600" onclick="deleteComponent('${componentId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <h2 class="component-header text-xl font-bold text-gray-900">제목 텍스트</h2>
                </div>
            </div>
        `;
    }
    
    /**
     * Create textarea component HTML
     */
    function createTextareaComponent(componentId) {
        return `
            <div id="${componentId}" class="component-wrapper mb-4" data-type="textarea">
                <div class="bg-white border border-gray-200 rounded-md p-4 hover:border-blue-400 transition-colors relative group">
                    <div class="component-toolbar absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="w-6 h-6 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 mr-1" onclick="editComponent('${componentId}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="w-6 h-6 bg-red-500 text-white rounded text-xs hover:bg-red-600" onclick="deleteComponent('${componentId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <label class="component-label block text-sm font-medium text-gray-700 mb-2">텍스트 영역</label>
                    <textarea class="component-textarea w-full border border-gray-300 rounded-md px-3 py-2 text-sm" rows="4" placeholder="텍스트를 입력하세요..."></textarea>
                </div>
            </div>
        `;
    }
    
    /**
     * Create dropdown component HTML
     */
    function createDropdownComponent(componentId) {
        return `
            <div id="${componentId}" class="component-wrapper mb-4" data-type="dropdown">
                <div class="bg-white border border-gray-200 rounded-md p-4 hover:border-blue-400 transition-colors relative group">
                    <div class="component-toolbar absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="w-6 h-6 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 mr-1" onclick="editComponent('${componentId}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="w-6 h-6 bg-red-500 text-white rounded text-xs hover:bg-red-600" onclick="deleteComponent('${componentId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <label class="component-label block text-sm font-medium text-gray-700 mb-2">드롭다운</label>
                    <select class="component-select w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="">옵션을 선택하세요</option>
                        <option value="option1">옵션 1</option>
                        <option value="option2">옵션 2</option>
                        <option value="option3">옵션 3</option>
                    </select>
                </div>
            </div>
        `;
    }
    
    /**
     * Create checkbox component HTML
     */
    function createCheckboxComponent(componentId) {
        return `
            <div id="${componentId}" class="component-wrapper mb-4" data-type="checkbox">
                <div class="bg-white border border-gray-200 rounded-md p-4 hover:border-blue-400 transition-colors relative group">
                    <div class="component-toolbar absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="w-6 h-6 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 mr-1" onclick="editComponent('${componentId}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="w-6 h-6 bg-red-500 text-white rounded text-xs hover:bg-red-600" onclick="deleteComponent('${componentId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" class="component-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label class="component-label ml-2 text-sm text-gray-700">체크박스 옵션</label>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Create radio group component HTML
     */
    function createRadioGroupComponent(componentId) {
        return `
            <div id="${componentId}" class="component-wrapper mb-4" data-type="radiogroup">
                <div class="bg-white border border-gray-200 rounded-md p-4 hover:border-blue-400 transition-colors relative group">
                    <div class="component-toolbar absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="w-6 h-6 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 mr-1" onclick="editComponent('${componentId}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="w-6 h-6 bg-red-500 text-white rounded text-xs hover:bg-red-600" onclick="deleteComponent('${componentId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <fieldset>
                        <legend class="component-label text-sm font-medium text-gray-700 mb-2">라디오 그룹</legend>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="radio" name="${componentId}_radio" class="h-4 w-4 text-blue-600 border-gray-300" value="option1">
                                <label class="ml-2 text-sm text-gray-700">옵션 1</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="${componentId}_radio" class="h-4 w-4 text-blue-600 border-gray-300" value="option2">
                                <label class="ml-2 text-sm text-gray-700">옵션 2</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="${componentId}_radio" class="h-4 w-4 text-blue-600 border-gray-300" value="option3">
                                <label class="ml-2 text-sm text-gray-700">옵션 3</label>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        `;
    }
    
    /**
     * Create generic component HTML
     */
    function createGenericComponent(componentId, componentType) {
        return `
            <div id="${componentId}" class="component-wrapper mb-4" data-type="${componentType}">
                <div class="bg-white border border-gray-200 rounded-md p-4 hover:border-blue-400 transition-colors relative group">
                    <div class="component-toolbar absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="w-6 h-6 bg-blue-500 text-white rounded text-xs hover:bg-blue-600 mr-1" onclick="editComponent('${componentId}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="w-6 h-6 bg-red-500 text-white rounded text-xs hover:bg-red-600" onclick="deleteComponent('${componentId}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="text-center py-4 text-gray-500">
                        <i class="fas fa-puzzle-piece text-2xl mb-2"></i>
                        <p class="text-sm font-medium">${componentType.charAt(0).toUpperCase() + componentType.slice(1)}</p>
                        <p class="text-xs">컴포넌트 자리표시자</p>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Get default properties for component type
     */
    function getDefaultProperties(componentType) {
        const defaults = {
            input: { label: '텍스트 입력', name: 'text_input', placeholder: '텍스트를 입력하세요...', required: false },
            button: { label: '버튼', text: '버튼', type: 'button' },
            header: { text: '제목 텍스트', level: 'h2' },
            textarea: { label: '텍스트 영역', name: 'text_area', placeholder: '텍스트를 입력하세요...', rows: 4 },
            dropdown: { label: '드롭다운', name: 'dropdown', options: ['옵션 1', '옵션 2', '옵션 3'] },
            checkbox: { label: '체크박스 옵션', name: 'checkbox', value: 'option' },
            radiogroup: { label: '라디오 그룹', name: 'radio_group', options: ['옵션 1', '옵션 2', '옵션 3'] }
        };
        
        return defaults[componentType] || { label: componentType, name: componentType };
    }
    
    /**
     * Select component
     */
    function selectComponent($component) {
        // Remove previous selection
        $('.component-wrapper').removeClass('selected');
        
        // Add selection
        $component.addClass('selected');
        selectedComponent = $component;
        
        // Show properties panel
        showComponentProperties($component);
    }
    
    /**
     * Show component properties
     */
    function showComponentProperties($component) {
        const componentType = $component.data('type');
        const componentId = $component.attr('id');
        
        $('#no-selection').hide();
        $('#component-properties').show();
        
        // Fill basic properties
        $('#prop-type').val(componentType);
        
        // Fill component-specific properties
        fillComponentProperties($component, componentType);
    }
    
    /**
     * Fill component properties based on type
     */
    function fillComponentProperties($component, componentType) {
        switch(componentType) {
            case 'input':
                $('#prop-label').val($component.find('.component-label').text());
                $('#prop-placeholder').val($component.find('.component-input').attr('placeholder'));
                break;
            case 'button':
                $('#prop-label').val($component.find('.component-button').text());
                break;
            case 'header':
                $('#prop-label').val($component.find('.component-header').text());
                break;
            case 'textarea':
                $('#prop-label').val($component.find('.component-label').text());
                $('#prop-placeholder').val($component.find('.component-textarea').attr('placeholder'));
                break;
            // Add more cases as needed
        }
    }
    
    /**
     * Apply component properties
     */
    function applyComponentProperties() {
        if (!selectedComponent) return;
        
        const componentType = selectedComponent.data('type');
        const label = $('#prop-label').val();
        const placeholder = $('#prop-placeholder').val();
        
        switch(componentType) {
            case 'input':
                selectedComponent.find('.component-label').text(label);
                selectedComponent.find('.component-input').attr('placeholder', placeholder);
                break;
            case 'button':
                selectedComponent.find('.component-button').text(label);
                break;
            case 'header':
                selectedComponent.find('.component-header').text(label);
                break;
            case 'textarea':
                selectedComponent.find('.component-label').text(label);
                selectedComponent.find('.component-textarea').attr('placeholder', placeholder);
                break;
        }
        
        // Update form data
        updateFormData();
        
        // Show success message
        showNotification('속성이 성공적으로 적용되었습니다', 'success');
    }
    
    /**
     * Switch property tab
     */
    function switchPropertyTab(tab) {
        $('.property-tab').removeClass('active border-blue-500 text-blue-600').addClass('border-transparent text-gray-500');
        $(`.property-tab[data-tab="${tab}"]`).addClass('active border-blue-500 text-blue-600').removeClass('border-transparent text-gray-500');
        
        $('.tab-content').hide();
        $(`#tab-${tab}`).show();
    }
    
    /**
     * Delete component
     */
    function deleteComponent(componentId) {
        if (confirm('이 컴포넌트를 삭제하시겠습니까?')) {
            $(`#${componentId}`).remove();
            
            // Remove from form data
            formData.components = formData.components.filter(comp => comp.id !== componentId);
            
            updateComponentCount();
            
            // Hide properties if this component was selected
            if (selectedComponent && selectedComponent.attr('id') === componentId) {
                $('#component-properties').hide();
                $('#no-selection').show();
                selectedComponent = null;
            }
            
            // Show empty state if no components
            if (formData.components.length === 0) {
                showEmptyState();
            }
        }
    }
    
    /**
     * Update component count
     */
    function updateComponentCount() {
        $('#component-count').text(`컴포넌트: ${formData.components.length}개`);
    }
    
    /**
     * Hide empty state
     */
    function hideEmptyState() {
        $('#empty-state').hide();
        $('#form-components').show();
    }
    
    /**
     * Show empty state
     */
    function showEmptyState() {
        $('#form-components').hide();
        $('#empty-state').show();
    }
    
    /**
     * Save form
     */
    function saveForm() {
        updateFormData();
        
        if (!formData.name) {
            showSaveModal();
            return;
        }
        
        saveFormToServer();
    }
    
    /**
     * Show save modal
     */
    function showSaveModal() {
        $('#save-form-modal').removeClass('hidden');
        $('#form-name-input').val(formData.name || '');
        $('#form-description-input').val(formData.description || '');
    }
    
    /**
     * Hide save modal
     */
    function hideSaveModal() {
        $('#save-form-modal').addClass('hidden');
    }
    
    /**
     * Save form with name
     */
    function saveFormWithName() {
        const name = $('#form-name-input').val().trim();
        const description = $('#form-description-input').val().trim();
        
        if (!name) {
            showNotification('폼 이름을 입력해주세요', 'error');
            return;
        }
        
        formData.name = name;
        formData.description = description;
        
        hideSaveModal();
        saveFormToServer();
    }
    
    /**
     * Save form to server
     */
    function saveFormToServer() {
        updateFormData();
        
        $.ajax({
            url: '/api/sandbox/form-creator/save',
            method: 'POST',
            data: {
                filename: formData.name.toLowerCase().replace(/[^a-z0-9]/g, '_') + '.json',
                name: formData.name,
                description: formData.description,
                data: JSON.stringify(formData)
            },
            success: function(response) {
                showNotification('폼이 성공적으로 저장되었습니다!', 'success');
                $('#form-status').text('Saved');
                loadFormsList(); // Refresh forms list
            },
            error: function(xhr) {
                showNotification('폼 저장 중 오류가 발생했습니다: ' + (xhr.responseJSON?.message || '알 수 없는 오류'), 'error');
            }
        });
    }
    
    /**
     * Export form
     */
    function exportForm() {
        updateFormData();
        
        const dataStr = JSON.stringify(formData, null, 2);
        const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
        
        const exportFileDefaultName = (formData.name || 'form') + '.json';
        
        const linkElement = document.createElement('a');
        linkElement.setAttribute('href', dataUri);
        linkElement.setAttribute('download', exportFileDefaultName);
        linkElement.click();
        
        showNotification('폼이 성공적으로 내보내졌습니다!', 'success');
    }
    
    /**
     * Import form
     */
    function importForm() {
        $('#import-file-input').click();
    }
    
    /**
     * Handle import file
     */
    function handleImportFile(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const importedData = JSON.parse(e.target.result);
                loadFormData(importedData);
                showNotification('폼을 성공적으로 가져왔습니다!', 'success');
            } catch (error) {
                showNotification('폼 가져오기 오류: 유효하지 않은 JSON 파일입니다', 'error');
            }
        };
        reader.readAsText(file);
        
        // Reset file input
        event.target.value = '';
    }
    
    /**
     * Load form data
     */
    function loadFormData(data) {
        formData = data;
        
        // Clear current form
        $('#form-components').empty();
        componentCounter = 0;
        
        // Recreate components
        if (data.components && data.components.length > 0) {
            data.components.forEach(component => {
                addComponentFromData(component);
            });
            hideEmptyState();
        } else {
            showEmptyState();
        }
        
        updateComponentCount();
        $('#form-status').text('Loaded');
    }
    
    /**
     * Add component from saved data
     */
    function addComponentFromData(componentData) {
        const componentId = componentData.id;
        componentCounter = Math.max(componentCounter, parseInt(componentId.replace('comp_', '')));
        
        let componentHtml = '';
        
        switch(componentData.type) {
            case 'input':
                componentHtml = createInputComponent(componentId);
                break;
            case 'button':
                componentHtml = createButtonComponent(componentId);
                break;
            case 'header':
                componentHtml = createHeaderComponent(componentId);
                break;
            case 'textarea':
                componentHtml = createTextareaComponent(componentId);
                break;
            case 'dropdown':
                componentHtml = createDropdownComponent(componentId);
                break;
            case 'checkbox':
                componentHtml = createCheckboxComponent(componentId);
                break;
            case 'radiogroup':
                componentHtml = createRadioGroupComponent(componentId);
                break;
            default:
                componentHtml = createGenericComponent(componentId, componentData.type);
        }
        
        $('#form-components').append(componentHtml);
        
        // Apply saved properties
        const $component = $('#' + componentId);
        applyComponentDataToElement($component, componentData);
    }
    
    /**
     * Apply component data to element
     */
    function applyComponentDataToElement($component, componentData) {
        const properties = componentData.properties || {};
        
        switch(componentData.type) {
            case 'input':
                if (properties.label) $component.find('.component-label').text(properties.label);
                if (properties.placeholder) $component.find('.component-input').attr('placeholder', properties.placeholder);
                break;
            case 'button':
                if (properties.text) $component.find('.component-button').text(properties.text);
                break;
            case 'header':
                if (properties.text) $component.find('.component-header').text(properties.text);
                break;
            case 'textarea':
                if (properties.label) $component.find('.component-label').text(properties.label);
                if (properties.placeholder) $component.find('.component-textarea').attr('placeholder', properties.placeholder);
                break;
        }
    }
    
    /**
     * Update form data
     */
    function updateFormData() {
        formData.components = [];
        
        $('#form-components .component-wrapper').each(function() {
            const $component = $(this);
            const componentId = $component.attr('id');
            const componentType = $component.data('type');
            const properties = extractComponentProperties($component, componentType);
            
            formData.components.push({
                id: componentId,
                type: componentType,
                properties: properties
            });
        });
    }
    
    /**
     * Extract component properties
     */
    function extractComponentProperties($component, componentType) {
        const properties = {};
        
        switch(componentType) {
            case 'input':
                properties.label = $component.find('.component-label').text();
                properties.placeholder = $component.find('.component-input').attr('placeholder');
                properties.name = $component.find('.component-input').attr('name') || '';
                break;
            case 'button':
                properties.text = $component.find('.component-button').text();
                properties.type = $component.find('.component-button').attr('type') || 'button';
                break;
            case 'header':
                properties.text = $component.find('.component-header').text();
                properties.level = $component.find('.component-header').prop('tagName').toLowerCase();
                break;
            case 'textarea':
                properties.label = $component.find('.component-label').text();
                properties.placeholder = $component.find('.component-textarea').attr('placeholder');
                properties.rows = $component.find('.component-textarea').attr('rows') || 4;
                break;
        }
        
        return properties;
    }
    
    /**
     * Clear form
     */
    function clearForm() {
        if (confirm('모든 컴포넌트를 초기화하시겠습니까?')) {
            $('#form-components').empty();
            formData = { name: '', description: '', components: [], settings: {} };
            componentCounter = 0;
            selectedComponent = null;
            
            $('#component-properties').hide();
            $('#no-selection').show();
            showEmptyState();
            updateComponentCount();
            
            showNotification('폼이 초기화되었습니다', 'info');
        }
    }
    
    /**
     * Show form manager
     */
    function showFormManager() {
        $('#form-manager-modal').removeClass('hidden');
        loadFormsList();
    }
    
    /**
     * Hide form manager
     */
    function hideFormManager() {
        $('#form-manager-modal').addClass('hidden');
    }
    
    /**
     * Load forms list
     */
    function loadFormsList() {
        $('#forms-loading').show();
        $('#forms-empty').hide();
        
        $.ajax({
            url: '/api/sandbox/form-creator/list',
            method: 'GET',
            success: function(response) {
                displayFormsList(response.forms || []);
            },
            error: function(xhr) {
                console.error('Error loading forms:', xhr);
                displayFormsList([]);
            }
        });
    }
    
    /**
     * Display forms list
     */
    function displayFormsList(forms) {
        $('#forms-loading').hide();
        
        const $formsList = $('#forms-list');
        
        if (forms.length === 0) {
            $('#forms-empty').show();
            return;
        }
        
        // Clear existing items (keep loading and empty states)
        $formsList.find('.form-item').remove();
        
        forms.forEach(form => {
            const $formItem = createFormListItem(form);
            $formsList.append($formItem);
        });
    }
    
    /**
     * Create form list item
     */
    function createFormListItem(form) {
        const $template = $('#form-item-template .form-item').clone();
        
        $template.data('filename', form.filename);
        $template.find('.form-name').text(form.name || form.filename);
        $template.find('.form-description').text(form.description || 'No description');
        $template.find('.form-created').text(formatDate(form.created_at));
        $template.find('.form-modified').text(formatDate(form.modified_at));
        $template.find('.form-components').text(form.component_count || 0);
        
        return $template;
    }
    
    /**
     * Format date
     */
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }
    
    /**
     * Load form
     */
    function loadForm(filename) {
        $.ajax({
            url: '/api/sandbox/form-creator/load/' + filename,
            method: 'GET',
            success: function(response) {
                const formData = JSON.parse(response.data);
                loadFormData(formData);
                hideFormManager();
                showNotification('폼을 성공적으로 불러왔습니다!', 'success');
            },
            error: function(xhr) {
                showNotification('폼 불러오기 오류: ' + (xhr.responseJSON?.message || '알 수 없는 오류'), 'error');
            }
        });
    }
    
    /**
     * Delete form
     */
    function deleteForm(filename) {
        if (confirm('이 폼을 삭제하시겠습니까?')) {
            $.ajax({
                url: '/api/sandbox/form-creator/delete/' + filename,
                method: 'DELETE',
                success: function(response) {
                    showNotification('폼이 성공적으로 삭제되었습니다!', 'success');
                    loadFormsList();
                },
                error: function(xhr) {
                    showNotification('폼 삭제 오류: ' + (xhr.responseJSON?.message || '알 수 없는 오류'), 'error');
                }
            });
        }
    }
    
    /**
     * Export saved form
     */
    function exportSavedForm(filename) {
        $.ajax({
            url: '/api/sandbox/form-creator/load/' + filename,
            method: 'GET',
            success: function(response) {
                const dataStr = response.data;
                const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
                
                const linkElement = document.createElement('a');
                linkElement.setAttribute('href', dataUri);
                linkElement.setAttribute('download', filename);
                linkElement.click();
                
                showNotification('폼이 성공적으로 내보내졌습니다!', 'success');
            },
            error: function(xhr) {
                showNotification('Error exporting form: ' + xhr.responseJSON.message, 'error');
            }
        });
    }
    
    /**
     * Filter forms
     */
    function filterForms() {
        const query = $('#search-forms').val().toLowerCase();
        
        $('.form-item').each(function() {
            const $item = $(this);
            const name = $item.find('.form-name').text().toLowerCase();
            const description = $item.find('.form-description').text().toLowerCase();
            
            if (name.includes(query) || description.includes(query)) {
                $item.show();
            } else {
                $item.hide();
            }
        });
    }
    
    /**
     * New form
     */
    function newForm() {
        if (formData.components.length > 0 && !confirm('새 폼을 생성하시겠습니까? 저장되지 않은 변경사항이 손실됩니다.')) {
            return;
        }
        
        clearForm();
        hideFormManager();
        showNotification('새 폼이 생성되었습니다', 'info');
    }
    
    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500',
            warning: 'bg-yellow-500'
        };
        
        const $notification = $(`
            <div class="fixed top-4 right-4 ${colors[type]} text-white px-4 py-2 rounded-md shadow-lg z-50 notification">
                ${message}
            </div>
        `);
        
        $('body').append($notification);
        
        setTimeout(() => {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    /**
     * Update component order
     */
    function updateComponentOrder() {
        // This would be called after sortable update
        updateFormData();
    }
    
    /**
     * Update selected component
     */
    function updateSelectedComponent() {
        // Real-time property updates
        applyComponentProperties();
    }
    
    /**
     * Reset component properties
     */
    function resetComponentProperties() {
        if (!selectedComponent) return;
        
        const componentType = selectedComponent.data('type');
        const defaults = getDefaultProperties(componentType);
        
        // Reset form fields
        $('#prop-label').val(defaults.label || '');
        $('#prop-name').val(defaults.name || '');
        $('#prop-placeholder').val(defaults.placeholder || '');
        $('#prop-description').val('');
        $('#prop-required').prop('checked', false);
        $('#prop-disabled').prop('checked', false);
        $('#prop-hidden').prop('checked', false);
    }
    
    /**
     * Switch right panel tab
     */
    function switchRightPanelTab(tab) {
        $('.right-panel-tab').removeClass('active border-blue-500 text-blue-600').addClass('border-transparent text-gray-500');
        $(`.right-panel-tab[data-tab="${tab}"]`).addClass('active border-blue-500 text-blue-600').removeClass('border-transparent text-gray-500');
        
        $('.right-tab-content').hide();
        $(`#right-tab-${tab}`).show();
        
        // Load content based on tab
        if (tab === 'tree') {
            updateFormTree();
        } else if (tab === 'forms') {
            loadQuickFormsList();
        } else if (tab === 'settings') {
            loadFormSettings();
        }
    }
    
    /**
     * Update form tree
     */
    function updateFormTree() {
        const $treeComponents = $('#tree-components');
        const $treeEmpty = $('#tree-empty');
        const $componentCount = $('#tree-component-count');
        
        $treeComponents.empty();
        
        if (formData.components.length === 0) {
            $treeEmpty.show();
            $componentCount.text('컴포넌트 0개');
            return;
        }
        
        $treeEmpty.hide();
        $componentCount.text(`컴포넌트 ${formData.components.length}개`);
        
        formData.components.forEach((component, index) => {
            const $treeItem = createTreeItem(component, index);
            $treeComponents.append($treeItem);
        });
    }
    
    /**
     * Create tree item
     */
    function createTreeItem(component, index) {
        const icons = {
            'input': 'fas fa-edit',
            'button': 'fas fa-hand-pointer',
            'header': 'fas fa-heading',
            'textarea': 'fas fa-align-left',
            'dropdown': 'fas fa-chevron-down',
            'checkbox': 'fas fa-check-square',
            'radiogroup': 'fas fa-dot-circle'
        };
        
        const icon = icons[component.type] || 'fas fa-puzzle-piece';
        const label = component.properties?.label || component.type;
        
        return $(`
            <div class="tree-item cursor-pointer" data-component-id="${component.id}" data-level="1">
                <div class="flex items-center py-1 px-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                    <i class="${icon} mr-2 text-gray-500"></i>
                    <span class="flex-1">${label}</span>
                    <span class="text-xs text-gray-400">${component.type}</span>
                    <button class="ml-2 text-xs text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100" onclick="deleteComponent('${component.id}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `);
    }
    
    /**
     * Select tree item
     */
    function selectTreeItem() {
        const componentId = $(this).data('component-id');
        if (componentId) {
            const $component = $('#' + componentId);
            if ($component.length) {
                selectComponent($component);
                switchRightPanelTab('components');
            }
        }
    }
    
    /**
     * Expand all tree nodes
     */
    function expandAllTreeNodes() {
        $('.tree-item').show();
        $('.tree-node-toggle i').removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
    
    /**
     * Toggle tree node
     */
    function toggleTreeNode() {
        // Implementation for nested tree nodes if needed
    }
    
    /**
     * Load form settings
     */
    function loadFormSettings() {
        $('#settings-form-title').val(formData.name || '');
        $('#settings-form-description').val(formData.description || '');
        $('#settings-submit-text').val(formData.settings?.submitText || 'Submit');
        $('#settings-success-message').val(formData.settings?.successMessage || 'Form submitted successfully!');
        $('#settings-layout-style').val(formData.settings?.layoutStyle || 'vertical');
        $('#settings-theme').val(formData.settings?.theme || 'default');
        $('#settings-error-position').val(formData.settings?.errorPosition || 'below');
        
        // Checkboxes
        $('#settings-show-progress').prop('checked', formData.settings?.showProgress || false);
        $('#settings-show-labels').prop('checked', formData.settings?.showLabels !== false);
        $('#settings-required-asterisk').prop('checked', formData.settings?.requiredAsterisk !== false);
        $('#settings-validate-realtime').prop('checked', formData.settings?.validateRealtime || false);
        $('#settings-show-errors').prop('checked', formData.settings?.showErrors !== false);
    }
    
    /**
     * Update form settings
     */
    function updateFormSettings() {
        if (!formData.settings) {
            formData.settings = {};
        }
        
        formData.name = $('#settings-form-title').val();
        formData.description = $('#settings-form-description').val();
        formData.settings.submitText = $('#settings-submit-text').val();
        formData.settings.successMessage = $('#settings-success-message').val();
        formData.settings.layoutStyle = $('#settings-layout-style').val();
        formData.settings.theme = $('#settings-theme').val();
        formData.settings.errorPosition = $('#settings-error-position').val();
        
        // Checkboxes
        formData.settings.showProgress = $('#settings-show-progress').is(':checked');
        formData.settings.showLabels = $('#settings-show-labels').is(':checked');
        formData.settings.requiredAsterisk = $('#settings-required-asterisk').is(':checked');
        formData.settings.validateRealtime = $('#settings-validate-realtime').is(':checked');
        formData.settings.showErrors = $('#settings-show-errors').is(':checked');
    }
    
    /**
     * Apply form settings
     */
    function applyFormSettings() {
        updateFormSettings();
        applyThemeSettings();
        showNotification('설정이 성공적으로 적용되었습니다!', 'success');
    }
    
    /**
     * Reset form settings
     */
    function resetFormSettings() {
        formData.settings = {};
        loadFormSettings();
        applyThemeSettings();
        showNotification('설정이 기본값으로 초기화되었습니다', 'info');
    }
    
    /**
     * Apply theme settings to form
     */
    function applyThemeSettings() {
        const theme = formData.settings?.theme || 'default';
        const $formCanvas = $('#form-canvas');
        
        // Remove existing theme classes
        $formCanvas.removeClass('theme-default theme-minimal theme-modern theme-classic');
        
        // Add new theme class
        $formCanvas.addClass(`theme-${theme}`);
        
        // Apply layout style
        const layoutStyle = formData.settings?.layoutStyle || 'vertical';
        $formCanvas.removeClass('layout-vertical layout-horizontal layout-inline').addClass(`layout-${layoutStyle}`);
    }
    
    /**
     * Load quick forms list
     */
    function loadQuickFormsList() {
        const $quickFormsList = $('#quick-forms-list');
        const $quickFormsEmpty = $('#quick-forms-empty');
        
        $.ajax({
            url: '/api/sandbox/form-creator/list',
            method: 'GET',
            success: function(response) {
                displayQuickFormsList(response.forms || []);
            },
            error: function(xhr) {
                console.error('Error loading forms:', xhr);
                displayQuickFormsList([]);
            }
        });
    }
    
    /**
     * Display quick forms list
     */
    function displayQuickFormsList(forms) {
        const $quickFormsList = $('#quick-forms-list');
        const $quickFormsEmpty = $('#quick-forms-empty');
        
        $quickFormsList.empty();
        
        if (forms.length === 0) {
            $quickFormsEmpty.show();
            return;
        }
        
        $quickFormsEmpty.hide();
        
        forms.slice(0, 10).forEach(form => { // Show only first 10 forms
            const $formItem = createQuickFormItem(form);
            $quickFormsList.append($formItem);
        });
    }
    
    /**
     * Create quick form item
     */
    function createQuickFormItem(form) {
        const date = new Date(form.modified_at).toLocaleDateString();
        return $(`
            <div class="quick-form-item bg-gray-50 p-3 rounded-lg hover:bg-gray-100 cursor-pointer" data-filename="${form.filename}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h5 class="font-medium text-sm text-gray-900 truncate">${form.name}</h5>
                        <p class="text-xs text-gray-500 mt-1 truncate">${form.description || 'No description'}</p>
                        <div class="flex items-center mt-2 text-xs text-gray-400">
                            <i class="fas fa-calendar mr-1"></i>
                            <span>${date}</span>
                            <span class="mx-2">•</span>
                            <i class="fas fa-puzzle-piece mr-1"></i>
                            <span>컴포넌트 ${form.component_count}개</span>
                        </div>
                    </div>
                    <div class="flex ml-2">
                        <button class="quick-load-form text-blue-600 hover:text-blue-800 p-1" data-filename="${form.filename}">
                            <i class="fas fa-folder-open text-xs"></i>
                        </button>
                        <button class="quick-delete-form text-red-600 hover:text-red-800 p-1 ml-1" data-filename="${form.filename}">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        `);
    }
    
    /**
     * Quick filter forms
     */
    function quickFilterForms() {
        const query = $('#quick-search-forms').val().toLowerCase();
        
        $('.quick-form-item').each(function() {
            const $item = $(this);
            const name = $item.find('h5').text().toLowerCase();
            const description = $item.find('p').text().toLowerCase();
            
            if (name.includes(query) || description.includes(query)) {
                $item.show();
            } else {
                $item.hide();
            }
        });
    }
    
    // Update component count when components change
    const originalAddComponentToCanvas = addComponentToCanvas;
    addComponentToCanvas = function(componentType) {
        originalAddComponentToCanvas(componentType);
        updateFormTree();
    };
    
    // Update tree when component is deleted  
    const originalDeleteComponent = deleteComponent;
    deleteComponent = function(componentId) {
        originalDeleteComponent(componentId);
        updateFormTree();
    };
    
    // Add event handlers for quick forms
    $(document).on('click', '.quick-load-form', function(e) {
        e.stopPropagation();
        const filename = $(this).data('filename');
        loadForm(filename);
    });
    
    $(document).on('click', '.quick-delete-form', function(e) {
        e.stopPropagation();
        const filename = $(this).data('filename');
        deleteForm(filename);
    });
    
    $(document).on('click', '.quick-form-item', function(e) {
        if (!$(e.target).closest('button').length) {
            const filename = $(this).data('filename');
            loadForm(filename);
        }
    });
    
    // Global functions for onclick handlers
    window.editComponent = function(componentId) {
        const $component = $('#' + componentId);
        selectComponent($component);
    };
    
    window.deleteComponent = function(componentId) {
        deleteComponent(componentId);
    };
});
</script>