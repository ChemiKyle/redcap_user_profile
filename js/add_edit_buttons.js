$(document).ready(function() {
    var settings = userProfile.addEditButtons;
    setUserProfileButton();

    // Handles clicks on user links from "Browse Users" page.
    $(this).ajaxStop(function () {
        setUserProfileButton();
    });

    function setUserProfileButton() {
        var username = getUrlParam('username');
        if (!username) {
            return false;
        }

        if ($('#user-profile-btn').length !== 0) {
            return false;
        }

        var $form = $('#edit_user_form');
        if ($form.length === 0) {
            return false;
        }

        if (typeof settings.existingProfiles[username] === 'undefined') {
            var button = settings.addButton;
            var url = settings.url + '&auto=1&user_profile_username=' + username + '&id=' + settings.nextProfileId;
        }
        else {
            var button = settings.editButton;
            var url = settings.url + '&id=' + settings.existingProfiles[username];
        }

        $form.append(button);
        document.getElementById('user-profile-btn').onclick = function() {
            location.href = url;
        };
    }

    /**
     * Source code: https://www.sitepoint.com/url-parameters-jquery.
     */
    function getUrlParam(name) {
        var results = new RegExp('[\?&]' + name + '=([^]*)').exec(window.location.href);
        if (results == null) {
           return null;
        }
        else {
           return results[1] || 0;
        }
    }
});
