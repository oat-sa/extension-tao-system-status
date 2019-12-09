module.exports = function (grunt) {
    'use strict';

    var sass    = grunt.config('sass') || {};
    var watch   = grunt.config('watch') || {};
    var notify  = grunt.config('notify') || {};
    var root    = grunt.option('root') + '/taoSystemStatus/views/';

    sass.taosync = {
        options : {},
        files : {}
    };

    sass.taosync.files[root + 'css/systemstatus.css'] = root + 'scss/systemstatus.scss';

    watch.taosyncsass = {
        files : [
            root + 'scss/**/*.scss',
        ],
        tasks : ['sass:taosystemstatus', 'notify:taosystemstatussass'],
        options : {
            debounceDelay : 1000
        }
    };

    notify.taosyncsass = {
        options: {
            title: 'Grunt SASS',
            message: 'SASS files compiled to CSS'
        }
    };

    grunt.config('sass', sass);
    grunt.config('watch', watch);
    grunt.config('notify', notify);

    grunt.registerTask('taosystemstatussass', ['sass:taosystemstatus']);
};
