var config = {
    paths: {
        'nbangular': 'Netbaseteam_Onlinedesign/js/assets/libs/angular-1.6.9.min',
        "nbdesigner": "Netbaseteam_Onlinedesign/js/assets/js/designer.min",
        'fabric': 'Netbaseteam_Onlinedesign/js/assets/js/fabric',
        'nblodash': 'Netbaseteam_Onlinedesign/js/assets/js/lodash',
        'modernbundle': 'Netbaseteam_Onlinedesign/js/assets/js/bundle-modern.min',
        'designmodern': 'Netbaseteam_Onlinedesign/js/assets/js/designer-modern.min',
        'curved': 'Netbaseteam_Onlinedesign/js/assets/js/curved',
        'fontface': 'Netbaseteam_Onlinedesign/js/assets/js/font-face',
        'imagesLoaded': 'Netbaseteam_Onlinedesign/js/assets/js/images-loaded',
        'masonry': 'Netbaseteam_Onlinedesign/js/assets/js/masonry',
        'bridget': 'Netbaseteam_Onlinedesign/js/assets/js/bridget',
        'perfect-scrollbar': 'Netbaseteam_Onlinedesign/js/assets/js/perfect-scrollbar',
        'qrcode': 'Netbaseteam_Onlinedesign/js/assets/js/qr-code',
        'spectrum': 'Netbaseteam_Onlinedesign/js/assets/js/spectrum',
        'angular-spectrum': 'Netbaseteam_Onlinedesign/js/assets/js/angular-spectrum',
        'tooltipster': 'Netbaseteam_Onlinedesign/js/assets/js/tooltipster',
        'Webcam': 'Netbaseteam_Onlinedesign/js/assets/js/webcam',
        'modern': 'Netbaseteam_Onlinedesign/js/assets/js/app-modern.min',
        'nbvista': 'Netbaseteam_Onlinedesign/js/assets/js/vista',
        'jqueryUiBlock': 'Netbaseteam_Onlinedesign/js/assets/js/jquery-blockui/jquery.blockUI',
        'photoswipe': 'Netbaseteam_Onlinedesign/js/assets/libs/photoswipe',
        'photoswipe-ui-default': 'Netbaseteam_Onlinedesign/js/assets/libs/photoswipe-ui-default'
    },
    shim: {
        "nbdesigner": {
            deps: ['jquery']
        },
        "jqueryUiBlock": {
            deps: ['jquery']
        },
        "photoswipe": {
            deps: ['jquery']
        },
        "photoswipe-ui-default": {
            deps: ['jquery']
        },
        "nblodash": {
            deps: ['underscore'],
            exports: "_"
        },
        "nbangular": {
            exports: "angular"
        },
        "modernbundle": {
            deps: ['jquery']
        },
        "curved": {
            deps: ['jquery', 'fabric']
        },
        "fabric": {
            deps: ['jquery']
        },
        "fontface": {
            deps: ['jquery']
        },
        "masonry": {
            deps: ['jquery']
        },
        "bridget": {
            deps: ['jquery'],
            exports: 'bridget'
        },
        'imagesLoaded': {
            exports: 'imagesLoaded'
        },
        "qrcode": {
            deps: ['jquery']
        },
        "spectrum": {
            deps: ['jquery']
        },
        "angular-spectrum": {
            deps: ['nbangular', 'spectrum']
        },
        "tooltipster": {
            deps: ['jquery']
        },
        "Webcam": {
            deps: ['jquery']
        },
        "perfect-scrollbar": {
            deps: ['jquery']
        },
        "designmodern": {
            deps: ['jquery']
        },
        "nbvista": {
            deps: ['jquery']
        },
        "modern": {
            deps: ['jquery', 'nbangular', 'nbvista']
        }
    }
};