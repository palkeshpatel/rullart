/**
 * Home Page Editor - Quill Editor Setup
 * Also handles Product Long Description editors
 */
import Quill from 'quill'

// Wait for DOM to be ready
function initQuillEditors() {
    if (typeof Quill === 'undefined') {
        return;
    }
    // Import Quill's built-in icons
    const icons = Quill.import('ui/icons');

    // Replace Quill's built-in toolbar icons with Tabler icons
    icons['bold'] = '<i class="ti ti-bold fs-lg"></i>'
    icons['italic'] = '<i class="ti ti-italic fs-lg"></i>'
    icons['underline'] = '<i class="ti ti-underline fs-lg"></i>'
    icons['strike'] = '<i class="ti ti-strikethrough fs-lg"></i>'
    icons['list'] = '<i class="ti ti-list fs-lg"></i>'
    icons['bullet'] = '<i class="ti ti-list-ul fs-lg"></i>'
    icons['indent'] = '<i class="ti ti-indent-increase fs-lg"></i>'
    icons['outdent'] = '<i class="ti ti-indent-decrease fs-lg"></i>'
    icons['link'] = '<i class="ti ti-link fs-lg"></i>'
    icons['image'] = '<i class="ti ti-photo fs-lg"></i>'
    icons['video'] = '<i class="ti ti-video fs-lg"></i>'
    icons['code-block'] = '<i class="ti ti-code fs-lg"></i>'
    icons['clean'] = '<i class="ti ti-trash fs-lg"></i>'
    icons['color'] = '<i class="ti ti-paint fs-lg"></i>'
    icons['background'] = '<i class="ti ti-background fs-lg"></i>'
    icons['script']['super'] = '<i class="ti ti-superscript fs-lg"></i>'
    icons['script']['sub'] = '<i class="ti ti-subscript fs-lg"></i>'
    icons['blockquote'] = '<i class="ti ti-blockquote fs-lg"></i>'
    icons['align'][''] = '<i class="ti ti-align-left fs-lg"></i>'
    icons['align']['center'] = '<i class="ti ti-align-center fs-lg"></i>'
    icons['align']['right'] = '<i class="ti ti-align-right fs-lg"></i>'
    icons['align']['justify'] = '<i class="ti ti-align-justified fs-lg"></i>'
    icons['header']['1'] = '<i class="ti ti-h-1 fs-lg"></i>'
    icons['header']['2'] = '<i class="ti ti-h-2 fs-lg"></i>'
    icons['header']['3'] = '<i class="ti ti-h-3 fs-lg"></i>'
    icons['header'][''] = '<i class="ti ti-letter-t fs-lg"></i>'

    // Initialize Quill editor for Details (EN)
    const detailsEditor = document.getElementById('details-editor')
    if (detailsEditor) {
        window.detailsQuill = new Quill(detailsEditor, {
            theme: 'snow',
            modules: {
                'toolbar': [
                    [{'font': []}],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{'color': []}, {'background': []}],
                    [{'script': 'super'}, {'script': 'sub'}],
                    [{'header': [false, 1, 2, 3, 4, 5, 6]}],
                    ['blockquote', 'code-block'],
                    [{'list': 'ordered'}, {'list': 'bullet'}, {'indent': '-1'}, {'indent': '+1'}],
                    [{'align': []}],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            }
        });

        // Set initial content if exists
        const detailsInput = document.getElementById('details');
        if (detailsInput && detailsInput.value) {
            window.detailsQuill.root.innerHTML = detailsInput.value;
        }
    }

    // Initialize Quill editor for Details (AR)
    const detailsAREditor = document.getElementById('detailsAR-editor')
    if (detailsAREditor) {
        window.detailsARQuill = new Quill(detailsAREditor, {
            theme: 'snow',
            modules: {
                'toolbar': [
                    [{'font': []}],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{'color': []}, {'background': []}],
                    [{'script': 'super'}, {'script': 'sub'}],
                    [{'header': [false, 1, 2, 3, 4, 5, 6]}],
                    ['blockquote', 'code-block'],
                    [{'list': 'ordered'}, {'list': 'bullet'}, {'indent': '-1'}, {'indent': '+1'}],
                    [{'align': []}],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            }
        });

        // Set initial content if exists
        const detailsARInput = document.getElementById('detailsAR');
        if (detailsARInput && detailsARInput.value) {
            window.detailsARQuill.root.innerHTML = detailsARInput.value;
        }
    }

    // Initialize Quill editor for Product Long Description (EN)
    const longdescrEditor = document.getElementById('longdescr-editor')
    if (longdescrEditor) {
        // Get content from textarea before initializing
        const longdescrInput = document.getElementById('longdescr');
        const longdescrContent = longdescrInput ? longdescrInput.value : '';
        
        // Clear the editor div content before initializing Quill
        longdescrEditor.innerHTML = '';
        
        window.longdescrQuill = new Quill(longdescrEditor, {
            theme: 'snow',
            modules: {
                'toolbar': [
                    [{'font': []}],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{'color': []}, {'background': []}],
                    [{'script': 'super'}, {'script': 'sub'}],
                    [{'header': [false, 1, 2, 3, 4, 5, 6]}],
                    ['blockquote', 'code-block'],
                    [{'list': 'ordered'}, {'list': 'bullet'}, {'indent': '-1'}, {'indent': '+1'}],
                    [{'align': []}],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            }
        });

        // Set initial content if exists
        if (longdescrContent) {
            window.longdescrQuill.root.innerHTML = longdescrContent;
        }
    }

    // Initialize Quill editor for Product Long Description (AR)
    const longdescrAREditor = document.getElementById('longdescrAR-editor')
    if (longdescrAREditor) {
        // Get content from textarea before initializing
        const longdescrARInput = document.getElementById('longdescrAR');
        const longdescrARContent = longdescrARInput ? longdescrARInput.value : '';
        
        // Clear the editor div content before initializing Quill
        longdescrAREditor.innerHTML = '';
        
        window.longdescrARQuill = new Quill(longdescrAREditor, {
            theme: 'snow',
            modules: {
                'toolbar': [
                    [{'font': []}],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{'color': []}, {'background': []}],
                    [{'script': 'super'}, {'script': 'sub'}],
                    [{'header': [false, 1, 2, 3, 4, 5, 6]}],
                    ['blockquote', 'code-block'],
                    [{'list': 'ordered'}, {'list': 'bullet'}, {'indent': '-1'}, {'indent': '+1'}],
                    [{'align': []}],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            }
        });

        // Set initial content if exists
        if (longdescrARContent) {
            window.longdescrARQuill.root.innerHTML = longdescrARContent;
        }
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQuillEditors);
} else {
    // DOM is already ready
    initQuillEditors();
}

