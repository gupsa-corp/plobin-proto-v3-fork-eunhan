<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FormEngine Demo - Sandbox</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- jQuery for better compatibility -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery UI for drag and drop -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
    <!-- SortableJS for drag and drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-builder-layout {
            height: calc(100vh - 60px);
            overflow: hidden;
        }
        .scrollable-content {
            max-height: calc(100vh - 140px);
            overflow-y: auto;
        }
        .component-item:hover {
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }
        .drag-over {
            border-color: #3b82f6 !important;
            background-color: #eff6ff !important;
        }
        
        /* Right Panel Tabs */
        .right-panel-tab.active {
            border-bottom-width: 2px;
            border-color: #3b82f6;
            color: #2563eb;
        }
        
        /* Tree Styles */
        .tree-item[data-level="1"] {
            margin-left: 1rem;
        }
        
        .tree-item:hover .group-hover\:opacity-100 {
            opacity: 1 !important;
        }
        
        /* Form Canvas Themes */
        .theme-minimal #form-components .component-wrapper > div {
            border: 1px solid #e5e7eb;
            border-radius: 0.25rem;
            box-shadow: none;
        }
        
        .theme-modern #form-components .component-wrapper > div {
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .theme-classic #form-components .component-wrapper > div {
            border: 2px solid #374151;
            border-radius: 0;
            background-color: #f9fafb;
        }
        
        /* Layout Styles */
        .layout-horizontal #form-components {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .layout-horizontal #form-components .component-wrapper {
            flex: 1;
            margin-bottom: 0;
            min-width: 200px;
        }
        
        .layout-inline #form-components {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .layout-inline #form-components .component-wrapper {
            flex: 0 0 auto;
            margin-bottom: 0;
        }
        
        .layout-inline #form-components .component-wrapper > div {
            padding: 0.5rem;
        }
        
        /* Quick Forms Items */
        .quick-form-item {
            transition: all 0.2s ease;
        }
        
        .quick-form-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Enhanced Sortable Styles */
        .sorting-active {
            transform: rotate(2deg) scale(1.05);
            z-index: 1000;
            opacity: 0.9;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
        }
        
        .sorting-inactive {
            opacity: 0.6;
            transition: opacity 0.2s ease;
        }
        
        .sortable-placeholder {
            background: linear-gradient(45deg, #dbeafe 25%, transparent 25%), 
                        linear-gradient(-45deg, #dbeafe 25%, transparent 25%), 
                        linear-gradient(45deg, transparent 75%, #dbeafe 75%), 
                        linear-gradient(-45deg, transparent 75%, #dbeafe 75%);
            background-size: 10px 10px;
            background-position: 0 0, 0 5px, 5px -5px, -5px 0px;
            border: 2px dashed #3b82f6;
            border-radius: 8px;
            margin-bottom: 1rem;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sortable-placeholder::before {
            content: '여기에 놓기';
            color: #3b82f6;
            font-weight: 500;
            font-size: 14px;
        }
        
        .component-wrapper {
            transition: all 0.2s ease;
            cursor: move;
        }
        
        .component-wrapper:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Top Navigation Bar -->
    @include('700-page-sandbox.709-page-form-creator.100-header-main')
    
    <!-- Main Form Builder Layout -->
    <div class="form-builder-layout flex">
        <!-- Left Panel - Components Palette -->
        <div class="w-80 bg-white border-r border-gray-200">
            @include('700-page-sandbox.709-page-form-creator.200-components-palette')
        </div>
        
        <!-- Center Panel - Form Builder Area -->
        <div class="flex-1 bg-gray-50">
            @include('700-page-sandbox.709-page-form-creator.300-form-builder-area')
        </div>
        
        <!-- Right Panel - Properties Panel -->
        <div class="w-80 bg-white border-l border-gray-200">
            @include('700-page-sandbox.709-page-form-creator.400-properties-panel')
        </div>
    </div>
    
    <!-- Hidden Form Manager Modal -->
    @include('700-page-sandbox.709-page-form-creator.500-form-manager-modal')

    @include('700-page-sandbox.709-page-form-creator.900-scripts')
</body>
</html>