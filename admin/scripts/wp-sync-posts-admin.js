import 'jquery';
import repeater from './repeater';
import settings from './admin-settings';
import postMetaBox from './post-meta-box';

repeater();
(function ($) {
    $(window).load(function () {
        settings.init();
        postMetaBox.init();
    });
})(jQuery);
