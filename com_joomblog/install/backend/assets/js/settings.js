jQuery(document).ready(function() {
    jQuery('#viewTabs a:first').tab('show');
    jQuery('#viewSocialTabs a:first').tab('show');

    jQuery('#jform_useRSSFeed0').click(function() {
        jQuery('#jform_rssFeedLimit, #jform_titleFeed').attr("disabled", "disabled");
    });
    jQuery('#jform_useRSSFeed1').click(function() {
        jQuery('#jform_rssFeedLimit, #jform_titleFeed').removeAttr("disabled");
    });
    jQuery("#jform_showFeed0, #jform_showFeed1").click(function () {
        jQuery("#jform_countOfChars").attr("disabled", true);
    });
    jQuery("#jform_showFeed2").click(function () {
        jQuery("#jform_countOfChars").removeAttr("disabled");
    });

    jQuery("#jform_useComment0, #jform_useComment1").click(function () {
        jQuery("#jform_disqusSubDomain").attr("disabled", true);
    });
    jQuery("#jform_useComment2").click(function () {
        jQuery("#jform_disqusSubDomain").removeAttr("disabled");
    });

    jQuery("#jform_useCommentCaptcha0, #jform_useCommentCaptcha1").click(function () {
        jQuery("#jform_recaptcha_publickey, #jform_recaptcha_privatekey").attr("disabled", true);
    });
    jQuery("#jform_useCommentCaptcha2").click(function () {
        jQuery("#jform_recaptcha_publickey, #jform_recaptcha_privatekey").removeAttr("disabled");
    });

    jQuery("#jform_useFeedBurnerIntegration0").click(function () {
        jQuery("#jform_rssFeedBurner").attr("disabled", true);
    });
    jQuery("#jform_useFeedBurnerIntegration1").click(function () {
        jQuery("#jform_rssFeedBurner").removeAttr("disabled");
    });

    jQuery("#jform_allowNotification0").click(function() {
        jQuery("#jform_adminEmail").attr("disabled", true);
    });
    jQuery("#jform_allowNotification1").click(function () {
        jQuery("#jform_adminEmail").removeAttr("disabled");
    });
});