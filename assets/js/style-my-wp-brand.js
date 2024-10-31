jQuery(document).ready(($) => {

    /**
     * Set Admin bar logo
     * 
     * @version 1.0.0
     */
    $("#admin-bar-logo").on("click", () => {
        var image = wp.media({
            multiple: false,
            title: 'Upload Admin logo',
        }).open().on("select", (e) => {
            var uploadedImages = image.state().get("selection").first();
            var selectedImages = uploadedImages.toJSON();
            // console.log(selectedImages.url);
            $("#admin-bar-logo").text('');
            $("#show-admin-bar-logo").attr("src", selectedImages.url);
            $("#hidden-admin-bar-logo").attr("value", selectedImages.url);
        });
    });

    /**
     * Set login logo
     * 
     * @version 1.0.0
     */
    $("#login-logo").on("click", () => {

        var image = wp.media({
            multiple: false,
            title: 'Upload login logo',
        }).open().on("select", (e) => {
            var uploadedImages = image.state().get("selection").first();
            var selectedImages = uploadedImages.toJSON();
            // console.log(selectedImages.url);
            $("#login-logo").text('');
            $("#show-login-logo").attr("src", selectedImages.url);
            $("#hidden-login-logo").attr("value", selectedImages.url);
        });

    });

    /**
     * @ajax mwb-plugin-form
     * 
     * @version 1.0.0
     * @version 1.1.3 Implemented a nonce value in the data object to enhance security.
     */
    $("#mwb-plugins-form").submit((e) => {

        e.preventDefault();

        var plugins_data = [];
        $('input[name="plugins[]"]').each(function () {
            if (this.checked === true) {
                plugins_data.push(this.value);
            }
        });

        $.ajax({
            type: "post",
            url: mwb_ajax.url,
            data: {
                action: 'mwb_plugins_form',
                plugins: plugins_data,
                nonce: mwb_ajax.nonce,
            },
            success: (response) => {
                window.location.href = 'options-general.php?page=mwb-plugins&update=true';
            }
        });

    });

    /**
     * @ajax mwb-style-form
     * 
     * @version 1.0.0
     * @version 1.1.3 Implemented a nonce value in the data object to enhance security.
     */
    $("#mwb-style-form").submit((e) => {

        e.preventDefault();

        $.ajax({
            type: "post",
            url: mwb_ajax.url,
            data: {
                action: 'mwb_style_ajax',
                nonce: mwb_ajax.nonce,
                hidden_admin_bar_logo: $("#show-admin-bar-logo").attr("src") ? $("#show-admin-bar-logo").attr("src") : '',
                hidden_login_logo: $("#show-login-logo").attr("src") ? $("#show-login-logo").attr("src") : '',
            },
            success: (response) => {
                window.location.href = 'options-general.php?page=mwb-style&update=true';
            }
        });

    });

    /**
     * @ajax mwb-author-form
     * 
     * @version 1.0.0
     * @version 1.1.3 Implemented a nonce value in the data object to enhance security.
     */
    $("#mwb-author-form").submit((e) => {

        e.preventDefault();

        $.ajax({
            type: "post",
            url: mwb_ajax.url,
            data: {
                action: 'mwb_author_form',
                nonce: mwb_ajax.nonce,
                wp_version_hide: document.getElementById("wp-version-hide").checked === true ? 'on' : 'off',
                wp_admin_footer_text: $("#wp-admin-footer-text").val() ? $("#wp-admin-footer-text").val() : '',
                wp_admin_bar_howdy_text: $("#wp-admin-bar-howdy-text").val() ? $("#wp-admin-bar-howdy-text").val() : '',
            },
            success: (response) => {
                window.location.href = 'options-general.php?page=mwb-author&update=true';
            }
        });

    });

});