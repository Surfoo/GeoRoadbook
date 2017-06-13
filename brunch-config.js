'use strict';

exports.config = {
    optimize: false,
    paths: {
        watched: ["app"],
        public: "web"
    },
    files: {
        stylesheets: {
            "joinTo": "app.min.css"
        },
        javascripts: {
            "joinTo": "app.min.js"
        }
    },
    overrides: {
        production: {
            optimize: true,
            sourceMaps: false,
            plugins: {
                autoReload: {
                    enabled: false
                }
            }
        }
    }

}
