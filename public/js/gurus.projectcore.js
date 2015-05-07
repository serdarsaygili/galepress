///////////////////////////////////////////////////////////////////////////////////////
// INTERACTIVITY
var cInteractivity = new function () {

    this.objectName = "interactivity";

    this.doRequest = function (t, u, d, funcError) {
        return cAjax.doSyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcError);
    };

    this.doAsyncRequest = function (t, u, d, funcSuccess, funcError) {
        cAjax.doAsyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcSuccess, funcError, true);
    };

    this.clickOk = function () {
        var id = $("#modal-editor").attr("opener");
        var content = $("#editor").val();
        $("#comp-" + id + "-content").val(content);
        $("#modal-editor").addClass("hide");
        $("#modal-mask").addClass("hide");
        $("#editor").destroyEditor();
    };

    this.clickCancel = function () {
        $("#modal-editor").addClass("hide");
        $("#modal-mask").addClass("hide");
        $("#editor").destroyEditor();
    };

    this.openTransferModal = function (e) {

        var componentID = e.parents("li:first").attr("componentid");
        var pageNo = e.parents("li.page:first").attr("pageno");

        $(".transfer-modal .all, .transfer-modal .one").addClass("hide");

        if (typeof componentID === "undefined") {
            $(".transfer-modal .all").removeClass("hide");
        }
        else {
            var componentName = $("div.tree a[componentid='" + componentID + "']").html();
            var o = $(".transfer-modal .one span");
            var t = o.attr("text");
            t = t.replace("{component}", componentName);
            o.html(t);
            $(".transfer-modal .one").removeClass("hide");
        }

        $("#transferComponentID").val(componentID);
        $("#transferFrom").val(pageNo);
        $(".transfer-modal div p strong em").html(pageNo);

        $(".transfer-modal select option")
                .removeAttr("disabled")
                .eq(parseInt(pageNo) - 1)
                .attr("disabled", "disabled")
                .next()
                .prop('selected', true);

        if ($("#transferTo").hasClass('chzn-done')) {
            $("#transferTo").removeClass('chzn-done')
            $("#transferTo").next().remove();
        }
        $("#transferTo").chosen();
        $(".transfer-modal").removeClass("hide");
        $("#modal-mask").removeClass("hide");
    };

    this.closeTransferModal = function () {

        $(".transfer-modal").addClass("hide");
        $("#modal-mask").addClass("hide");
    };

    this.transfer = function () {
        var contentFileID = $("#contentfileid").val();
        var componentID = $("#transferComponentID").val();
        var from = $("#transferFrom").val();
        var to = $("#transferTo").val();
        this.saveCurrentPage();

        var t = 'POST';
        var u = '/' + $('#currentlanguage').val() + '/' + route["interactivity_transfer"];
        var d = "contentfileid=" + contentFileID + "&componentid=" + componentID + "&from=" + from + "&to=" + to;
        var ret = cInteractivity.doRequest(t, u, d);
        if (ret.getValue("success") == "true") {
            this.refreshTree();
            this.clearPage();
            this.closeTransferModal();
        }
        else {
            cNotification.failure(ret.getValue("errmsg"));
        }
    };

    this.openSettings = function () {

        $(".compression-settings").removeClass("hide");
        $("#modal-mask").removeClass("hide");
    };

    this.closeSettings = function (dontUpdate) {

        dontUpdate = (typeof dontUpdate == "undefined") ? false : dontUpdate;

        if (!dontUpdate)
        {
            $(".compression-settings div.checkbox").removeClass("checked");

            if ($("#included").val() == "1")
            {
                $(".compression-settings div.checkbox").addClass("checked");
            }
        }

        $(".compression-settings").addClass("hide");
        $("#modal-mask").addClass("hide");
    };

    this.saveSettings = function () {

        $("#included").val(0);

        if ($(".compression-settings div.checkbox").hasClass("checked"))
        {
            $("#included").val(1);
        }

        this.closeSettings(true);
    };
    this.refreshTree = function () {
        var contentFileID = $("#contentfileid").val();
        var t = 'POST';
        var u = '/' + $('#currentlanguage').val() + '/' + route["interactivity_refreshtree"];
        var d = "contentfileid=" + contentFileID;
        cInteractivity.doAsyncRequest(t, u, d, function (ret) {
            //Collapse destroy 
            $('div.tree a').unbind('click');
            $('#tabs-2').html(ret.getValue("html"));
            //Collapse init
            //initTree();
            $('div.tree').collapse(false, true);
            $('div.tree a.selectcomponent').click(function () {
                cInteractivity.selectComponent($(this));
            });
        });
    };

    this.selectComponent = function (obj) {

        var currentPageNo = $("#pageno").val();
        var pageNo = obj.parents("li.page:first").attr("pageno");

        if (pageNo !== currentPageNo) {
            this.showPage(pageNo, false, cInteractivity.selectComponentOnCurrentPage, obj);
        } else {
            this.selectComponentOnCurrentPage(obj);
        }
    };

    this.selectComponentOnCurrentPage = function (obj) {

        var id = obj.attr("componentid");
        var tool = $("#tool-" + id);
        if (!tool.hasClass("selected")) {
            //hide all other components
            $("#component-container .component").addClass("hide");

            //show only selected components
            $("#component-container #prop-" + id).removeClass("hide");

            $(".gselectable").removeClass("selected");
            tool.addClass("selected");
        }
    };

    this.deleteComponentOnCurrentPage = function () {
        $("#page div.modal-component, #page div.tooltip-trigger").each(function () {
            var id = $(this).attr("componentid");
            $("#prop-" + id + " div.component-header a.delete").click();
        });
    };

    this.showPage = function (pageno, dontSave, func, obj)
    {
        dontSave = (typeof dontSave == "undefined") ? false : dontSave;
        if (!dontSave) {
            this.saveCurrentPage();
        }

        $("#pdf-container").addClass("loading");
        $("#page").css("display", "none");

        //remove active class
        $("div.thumblist ul.slideshow-slides li.each-slide").each(function () {
            $(this).removeClass('active');
        });
        //add active class to current page
        $("div.thumblist ul.slideshow-slides li.each-slide a[pageno='" + pageno + "']").parents("li:first").addClass('active');

        var pageCount = $("div.thumblist ul.slideshow-slides li.each-slide").length;

        $("#pageno").val(pageno);
        $("#pdf-page").val(pageno + '/' + pageCount);

        var src = $("div.thumblist ul.slideshow-slides li.each-slide a[pageno='" + pageno + "'] img").attr("src");

        $("#page").smoothZoom('destroy');

        var img = new Image();
        img.onload = function () {
            $("#page")
                    .css("background", "url('" + src + "') no-repeat top left")
                    .css("width", img.width + "px")
                    .css("height", img.height + "px");

            cInteractivity.clearPage();
            cInteractivity.loadPage(pageno, func, obj);

            if (!$("html").hasClass("lt-ie9")) {
                var h = $(window).innerHeight() - $("#pdf-container").offset().top - $("footer").outerHeight();

                $('#page').smoothZoom({
                    width: '100%',
                    height: h + 'px',
                    responsive: true,
                    pan_BUTTONS_SHOW: "NO",
                    pan_LIMIT_BOUNDARY: "NO",
                    button_SIZE: 24,
                    button_ALIGN: "top right",
                    zoom_MAX: 500,
                    border_TRANSPARENCY: 0,
                    container: 'pdf-container',
                    max_WIDTH: '',
                    max_HEIGHT: ''
                });
            }
        };
        img.src = src;
    };

    this.clearPage = function () {
        $("#pdf-container .tooltip-trigger").remove();
        $("#pdf-container .modal-component").remove();

        $("#component-container").html("");
    };

    this.loadPage = function (pageno, func, obj)
    {
        var frm = $("#pagecomponents");
        var t = 'POST';
        var u = '/' + $('#currentlanguage').val() + '/' + route["interactivity_loadpage"];
        var d = cForm.serialize(frm);
        cInteractivity.doAsyncRequest(t, u, d, function (ret) {
            //Sayfa henuz yuklenmeden degistirilirse eski icerikleri gosterme!
            if (parseInt($("#pageno").val()) !== parseInt(pageno))
                return;

            $("#page").append(ret.getValue("tool"));
            $("#component-container").html(ret.getValue("prop"));
            //Collapse destroy
            $('div.tree a').unbind('click');

            $("#page div.modal-component, #page div.tooltip-trigger").each(function () {

                var componentName = $(this).attr("componentname");
                var id = $(this).attr("componentid");
                var arr = $(this).attr("data-position").split(',');
                var left = parseInt(arr[0]);
                var top = parseInt(arr[1]);
                var width = parseInt($("#prop-" + id + " input.w").val());
                var height = parseInt($("#prop-" + id + " input.h").val());

                if (componentName == "video" || componentName == "webcontent" || componentName == "slideshow" || componentName == "gal360") {
                    if ($(this).attr("id") == "tool-" + id) {
                        $(this).component({
                            left: left,
                            top: top,
                            width: width,
                            height: height
                        });
                    }
                } else {
                    $(this).component({
                        left: left,
                        top: top,
                        width: width,
                        height: height
                    });
                }
            });

            $('div.tree').collapse(false, true);
            $('div.tree a.selectcomponent').click(function () {
                cInteractivity.selectComponent($(this));
            });

            $("#pdf-container").removeClass("loading");
            $("#page").css("display", "block");

            if (func && (typeof func == "function")) {
                func(obj);
            }
        });
    };

    this.saveCurrentPage = function (closing) {
        closing = (typeof closing == "undefined") ? false : closing;

        var t = 'POST';
        var u = '/' + $('#currentlanguage').val() + '/' + route["interactivity_save"];
        var d = $("#pagecomponents").serialize() + "&closing=" + (closing ? 'true' : 'false');
        var ret = cInteractivity.doRequest(t, u, d);

        var d = new Date();
        var h = (d.getHours() > 9 ? "" + d.getHours() : "0" + d.getHours());
        var m = (d.getMinutes() > 9 ? "" + d.getMinutes() : "0" + d.getMinutes());
        var s = interactivity["autosave"]
                .replace("{hour}", h)
                .replace("{minute}", m);

        $("#pdf-save span.save-info").html(s);

        if (!closing) {
            cInteractivity.showPage($("#pageno").val(), true);
        }
    };

    this.saveAndClose = function () {
        this.saveCurrentPage(true);
        this.close();
    };

    this.close = function () {
        closeInteractiveIDE();
    };

    this.exitWithoutSave = function () {
        this.close();
    };

    this.hideAllInformation = function () {
        $("div.component-info div").addClass("hide");
    };

    this.selectItem = function () {
        $("div.component-info div").addClass("hide");
    };
};

///////////////////////////////////////////////////////////////////////////////////////
// USER
var cUser = new function () {
    this.objectName = "users";

    this.doRequest = function (t, u, d, funcError) {
        return cAjax.doSyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcError);
    };

    this.doAsyncRequest = function (t, u, d, funcSuccess, funcError) {
        cAjax.doAsyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcSuccess, funcError, true);
    };

    this.go2Login = function () {
        document.location.href = "/";
    };

    this.loginEvent = function (evt, func) {
        var keyCode = null;
        if (evt.which) {
            keyCode = evt.which;
        } else if (evt.keyCode) {
            keyCode = evt.keyCode;
        }
        if (13 == keyCode) {
            func();
            return false;
        }
        return true;
    };

    this.login = function () {
        cNotification.hide();

        var frm = $("form:first");
        var validate = cForm.validate(frm);
        if (validate) {

            cNotification.loader();

            var t = 'POST';
            var u = '/' + $('#currentlanguage').val() + '/' + route["login"];
            var d = cForm.serialize(frm);
            cUser.doAsyncRequest(t, u, d, function (ret) {
                cNotification.success(null, ret.getValue("msg"));

                document.location.href = '/' + $('#currentlanguage').val() + '/' + route["home"];
            });
        } else {
            cNotification.validation();
        }
    };

    this.forgotMyPassword = function () {

        cNotification.hide();

        var frm = $("form:first");
        var validate = cForm.validate(frm);
        if (validate) {

            cNotification.loader();

            var t = 'POST';
            var u = '/' + $('#currentlanguage').val() + '/' + route["forgotmypassword"];
            var d = cForm.serialize(frm);
            cUser.doAsyncRequest(t, u, d, function (ret) {
                cNotification.success(null, ret.getValue("msg"));
            });
        } else {
            cNotification.validation();
        }
    };

    this.resetMyPassword = function () {

        cNotification.hide();

        var frm = $("form:first");
        var validate = cForm.validate(frm);
        if (validate) {

            cNotification.loader();

            var t = 'POST';
            var u = '/' + $('#currentlanguage').val() + '/' + route["resetmypassword"];
            var d = cForm.serialize(frm);
            cUser.doAsyncRequest(t, u, d, function (ret) {
                cNotification.success(null, ret.getValue("msg"));
            });
        } else {
            cNotification.validation();
        }
    };

    this.saveMyDetail = function () {

        cNotification.hide();

        var frm = $("form:first");
        var validate = cForm.validate(frm);
        if ($("#Password").val() != $("#Password2").val()) {
            validate = false;
        }
        if (validate) {

            cNotification.loader();

            var t = 'POST';
            var u = '/' + $('#currentlanguage').val() + '/' + route["mydetail"];
            var d = cForm.serialize(frm);
            cUser.doAsyncRequest(t, u, d, function (ret) {
                cNotification.success();
            });
        } else {
            cNotification.validation();
        }
    };

    this.save = function () {
        cCommon.save(this.objectName);
    };

    this.erase = function () {
        cCommon.erase(this.objectName);
    };

    this.sendNewPassword = function () {

        cNotification.hide();

        var frm = $("form:first");
        var validate = cForm.validate(frm);
        if (validate) {

            cNotification.loader();

            var t = 'POST';
            var u = '/' + $('#currentlanguage').val() + '/' + route["users_send"];
            var d = cForm.serialize(frm);
            cUser.doAsyncRequest(t, u, d, function (ret) {
                cNotification.success();
            });
        } else {
            cNotification.validation();
        }
    };
};

///////////////////////////////////////////////////////////////////////////////////////
// CUSTOMER
var cCustomer = new function () {

    this.objectName = "customers";

    this.doRequest = function (t, u, d, funcError) {
        return cAjax.doSyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcError);
    };

    this.doAsyncRequest = function (t, u, d, funcSuccess, funcError) {
        cAjax.doAsyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcSuccess, funcError, true);
    };

    this.loadCustomerOptionList = function () {

        var t = 'GET';
        var u = '/' + $('#currentlanguage').val() + '/' + route["customers"];
        var d = "option=1";
        cCustomer.doAsyncRequest(t, u, d, function (ret) {
            $("#ddlCustomer").html(ret);
            $('#ddlCustomer').trigger('chosen:updated');
        });
    };

    this.CustomerOnChange = function (obj) {
        cReport.OnChange(obj);
        cApplication.loadApplicationOptionList();
    };

    this.save = function () {

        cCommon.save(this.objectName);
    };

    this.erase = function () {

        cCommon.erase(this.objectName);
    };
};

///////////////////////////////////////////////////////////////////////////////////////
// APPLICATION
var cApplication = new function () {

    this.objectName = "applications";

    this.doRequest = function (t, u, d, funcError) {
        return cAjax.doSyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcError);
    };

    this.doAsyncRequest = function (t, u, d, funcSuccess, funcError) {
        cAjax.doAsyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcSuccess, funcError, true);
    };

    this.loadApplicationOptionList = function () {

        var t = 'GET';
        var u = '/' + $('#currentlanguage').val() + '/' + route["applications"];
        var d = "customerID=" + $("#ddlCustomer").val() + "&option=1";
        cApplication.doAsyncRequest(t, u, d, function (ret) {
            $("#ddlApplication").html(ret);
            $('#ddlApplication').trigger('chosen:updated');
        });
    };

    this.ApplicationOnChange = function (obj) {
        cReport.OnChange(obj);
        cContent.loadContentOptionList();
    };

    this.save = function () {
        cCommon.save(this.objectName);
    };

    this.erase = function () {
        cCommon.erase(this.objectName);
    };

    this.pushNotification = function () {
        cNotification.hide();
        var frm = $("#formPushNotification");
        var applicationID = parseInt($("[name='ApplicationID']", frm).val());
        var url = '/' + $('#currentlanguage').val() + '/' + route["applications_pushnotification"];
        url = url.replace('(:num)', applicationID);
        var validate = cForm.validate(frm);
        if (validate) {
            cNotification.loader();
            var t = 'POST';
            var d = cForm.serialize(frm);
            cApplication.doAsyncRequest(t, url, d, function (ret) {
                $('#modalPushNotification').modal('hide');
                cNotification.success();
            });
        }
        else {
            cNotification.validation();
        }
    };

};

///////////////////////////////////////////////////////////////////////////////////////
// CONTENT
var cContent = new function () {
    var _self = this;
    this.objectName = "contents";
    this.doRequest = function (t, u, d, funcError) {
        return cAjax.doSyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcError);
    };

    this.doAsyncRequest = function (t, u, d, funcSuccess, funcError) {
        cAjax.doAsyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcSuccess, funcError, true);
    };

    this.loadContentOptionList = function () {
        var t = 'GET';
        var u = '/' + $('#currentlanguage').val() + '/' + route["contents"];
        var d = "applicationID=" + $("#ddlApplication").val() + "&option=1";
        cContent.doAsyncRequest(t, u, d, function (ret) {
            $("#ddlContent").html(ret);
            $('#ddlContent').trigger('chosen:updated');
        });
    };

    this.ContentOnChange = function (obj) {
        cReport.OnChange(obj);
    };

    this.save = function () {
        if (!$("#IsMaster").is(':checked') && $("#IsProtected").is(':checked')) {
            var t = 'GET';
            var u = '/' + $('#currentlanguage').val() + '/' + route["contents_passwords"];
            var d = "contentID=" + $("#ContentID").val() + '&type=qty';
            var ret = cContent.doRequest(t, u, d);
            if (parseInt(ret) > 0) {
                $("#Password").removeClass("required");
            } else {
                $("#Password").addClass("required");
            }
        }

        if (!$('#CategoryID_chosen ul.chosen-choices li').hasClass('search-choice')) {
            $('#dialog-category-warning').modal('show');
            return;
        }

        cCommon.save(
	    this.objectName, 
	    function (ret) {
		contentID = ret.getValue("contentID");
		cNotification.success();
		document.location.href = '/' + $('#currentlanguage').val() + '/' + route[_self.objectName] + '/' + contentID;
	    }
	);
    };

    this.erase = function () {
        cCommon.erase(this.objectName);
    };

    this.selectFile = function () {
        $('#File').click();
        return false;
    };

    this.selectCoverImage = function () {
        $('#CoverImageFile').click();
        return false;
    };

    this.openInteractiveIDE = function (cfID) {
        cNotification.loader();
        $('#btn_interactive').addClass('on');
    };

// Bu fonks. explorer 10 da iframe kapattığı için interactivity->html.blade body tagından kaldırıldı.
    this.closeInteractiveIDE = function () {
        var iframe = $("iframe#interactivity");
        if (iframe.css("display") == "block") {
            $(".loader").addClass("hidden");
            $("html").css("overflow", "scroll");
            iframe
                    .attr("src", "")
                    .css("display", "none");

            if (getFullscreenStatus()) {
                exitFullscreen();
            }

            setTimeout(function () {
                $('#btn_interactive').removeClass('on');
            }, 2000);
        }
    };

    //Content category
    this.CategoryOnChange = function (obj) {
        if (obj.val() == "-1") {
            $("div.list_container").removeClass("hidden");
            $("div.cta_container").removeClass("hidden");
            $("div.form_container").addClass("hidden");
            cContent.loadCategoryList();
            $("#dialog-category-form").removeClass("hidden");
            $('#dialog-category-form').modal('show');
        }
    };

    this.showCategoryList = function () {
        $("div.list_container").removeClass("hidden");
        $("div.cta_container").removeClass("hidden");
        $("div.form_container").addClass("hidden");
        cContent.loadCategoryList();
        $("#dialog-category-form").removeClass("hidden");
        $('#dialog-category-form').modal('show');
    };

    this.loadCategoryList = function () {
        var t = 'GET';
        var u = '/' + $('#currentlanguage').val() + '/' + route["categories"];
        var d = "appID=" + $("#ApplicationID").val();
        cContent.doAsyncRequest(t, u, d, function (ret) {
            $("#dialog-category-form table tbody").html(ret);
        });
    };

    this.loadCategoryOptionList = function () {
        var t = 'GET';
        var u = '/' + $('#currentlanguage').val() + '/' + route["categories"];
        var d = "appID=" + $("#ApplicationID").val() + '&contentID=' + $("#ContentID").val() + '&type=options';
        cContent.doAsyncRequest(t, u, d, function (ret) {
            $("#CategoryID").html(ret);
            $("#CategoryID").trigger("chosen:updated");
        });
    };

    this.addNewCategory = function () {
        var appID = $("#ApplicationID").val();
        $("#CategoryCategoryID").val("0");
        $("#CategoryApplicationID").val(appID);
        $("#CategoryName").val("");
        $("div.list_container").addClass("hidden");
        $("div.cta_container").addClass("hidden");
        $("div.form_container").removeClass("hidden");
    };

    this.selectCategory = function (id) {
        $("#CategoryID").val(id);
        $("#dialog-category-form").dialog("close");
    };

    this.modifyCategory = function (id) {
        var appID = $("#ApplicationID").val();
        var name = $("#category" + id + " td:eq(0)").html();
        $("#CategoryCategoryID").val(id);
        $("#CategoryApplicationID").val(appID);
        $("#CategoryName").val(name);
        $("div.list_container").addClass("hidden");
        $("div.cta_container").addClass("hidden");
        $("div.form_container").removeClass("hidden");
    };

    this.deleteCategory = function (id) {
        var t = 'POST';
        var u = '/' + $('#currentlanguage').val() + '/' + route["categories_delete"];
        var d = "CategoryID=" + id;
        cContent.doAsyncRequest(t, u, d, function (ret) {
            cContent.loadCategoryList();
            cContent.loadCategoryOptionList();
        });
    };

    this.saveCategory = function () {
        cNotification.hide();
        var frm = $("#dialog-category-form form:first");
        var validate = cForm.validate(frm);
        if (validate) {
            cNotification.loader();
            var t = 'POST';
            var u = '/' + $('#currentlanguage').val() + '/' + route["categories_save"];
            var d = cForm.serialize(frm);
            cContent.doAsyncRequest(t, u, d, function (ret) {
                $("div.list_container").removeClass("hidden");
                $("div.cta_container").removeClass("hidden");
                $("div.form_container").addClass("hidden");
                cContent.loadCategoryList();
                cContent.loadCategoryOptionList();
                cNotification.hide();
            });
        } else {
            cNotification.validation();
        }
    };

    //Content password
    this.showPasswordList = function () {
        $("div.list_container").removeClass("hidden");
        $("div.cta_container").removeClass("hidden");
        $("div.form_container").addClass("hidden");
        cContent.loadPasswordList();
        $("#dialog-password-form").removeClass("hidden");
        $('#dialog-password-form').modal('show');
    };

    this.loadPasswordList = function () {
        var t = 'GET';
        var u = '/' + $('#currentlanguage').val() + '/' + route["contents_passwords"];
        var d = "contentID=" + $("#ContentID").val();
        cContent.doAsyncRequest(t, u, d, function (ret) {
            $("#dialog-password-form table tbody").html(ret);
        });
    };

    this.addNewPassword = function () {
        var contentID = $("#ContentID").val();
        $("#ContentPasswordID").val("0");
        $("#ContentPasswordContentID").val(contentID);
        $("#ContentPasswordName").val("");
        $("#ContentPasswordPassword").val("");
        $("#ContentPasswordQty").val("1");
        $("div.list_container").addClass("hidden");
        $("div.cta_container").addClass("hidden");
        $("div.form_container").removeClass("hidden");
    };

    this.modifyPassword = function (id) {
        var contentID = $("#ContentID").val();
        var name = $("#contentpassword" + id + " td:eq(0)").html();
        var qty = $("#contentpassword" + id + " td:eq(1)").html();
        $("#ContentPasswordID").val(id);
        $("#ContentPasswordContentID").val(contentID);
        $("#ContentPasswordName").val(name);
        $("#ContentPasswordPassword").val("");
        $("#ContentPasswordQty").val(qty);
        $("div.list_container").addClass("hidden");
        $("div.cta_container").addClass("hidden");
        $("div.form_container").removeClass("hidden");
    };

    this.deletePassword = function (id) {
        var t = 'POST';
        var u = '/' + $('#currentlanguage').val() + '/' + route["contents_passwords_delete"];
        var d = "ContentPasswordID=" + id;
        cContent.doAsyncRequest(t, u, d, function (ret) {
            cContent.loadPasswordList();
        });
    };

    this.savePassword = function () {
        cNotification.hide();
        var frm = $("#dialog-password-form form:first");
        var validate = cForm.validate(frm);
        if (validate) {
            cNotification.loader();
            var t = 'POST';
            var u = '/' + $('#currentlanguage').val() + '/' + route["contents_passwords_save"];
            var d = cForm.serialize(frm);
            cContent.doAsyncRequest(t, u, d, function (ret) {
                $("div.list_container").removeClass("hidden");
                $("div.cta_container").removeClass("hidden");
                $("div.form_container").addClass("hidden");
                cContent.loadPasswordList();
                cNotification.hide();
            });
        } else {
            cNotification.validation();
        }
    };

    this.giveup = function () {
        $("div.list_container").removeClass("hidden");
        $("div.cta_container").removeClass("hidden");
        $("div.form_container").addClass("hidden");
    };
    
    this.addFileUpload = function() {
        if($("html").hasClass("lt-ie10")) {
        $("#File").uploadify({
                'swf': '/uploadify/uploadify.swf',
                'uploader': '/' + $('#currentlanguage').val() + '/' + route["contents_uploadfile2"],
                'cancelImg': '/uploadify/uploadify-cancel.png',
                'fileTypeDesc': 'PDF Files',
                'fileTypeExts': '*.pdf',
                'buttonText': "{{ __('common.contents_file_select') }}",
                'multi': false,
                'auto': true,
                'successTimeout': 300,
                'onSelect': function (file) {
                        $('#hdnFileSelected').val("1");
                        $("[for='File']").removeClass("hide");
                },
                'onUploadProgress': function (file, bytesUploaded, bytesTotal, totalBytesUploaded, totalBytesTotal) {
                        var progress = totalBytesUploaded / totalBytesTotal * 100;
                        if(progress > 99) {
                                progress = 100;
                        }
                        $("[for='File'] label").html(progress.toFixed(0) + '%');
                        $("[for='File'] div.scale").css('width', progress.toFixed(0) + '%');
                },
                'onUploadSuccess': function (file, data, response) {
                        if(data.getValue("success") == "true") {
                                var fileName = data.getValue("filename");

                                $('#hdnFileName').val(fileName);
                                $("[for='File']").addClass("hide");

                                $('#hdnCoverImageFileSelected').val("1");
                                $('#hdnCoverImageFileName').val(data.getValue("coverimagefilename"));
                                $('#imgPreview').attr("src", "/files/temp/" + data.getValue("coverimagefilename"));

                                $("div.rightbar").removeClass("hidden");

                                //auto save
                                if(parseInt($("#ContentID").val()) > 0) {
                                        cContent.save();
                                }
                        }
                },
                'onCancel': function(file) {
                        $("[for='File']").addClass("hide");
                }
        });
        } else {
            $("#File").fileupload({
                    url: '/' + $('#currentlanguage').val() + '/' + route["contents_uploadfile"],
                    dataType: 'json',
                    sequentialUploads: true,
                    formData: { 
                            'element': 'File'
                    },
                    add: function(e, data) {
                            if(/\.(pdf)$/i.test(data.files[0].name)) {
                                    $('#hdnFileSelected').val("1");
                                    $("[for='File']").removeClass("hide");

                                    data.context = $("[for='File']");
                                    data.context.find('a').click(function(e){
                                            e.preventDefault();
                                            var template = $("[for='File']");
                                            data = template.data('data') || {};
                                            if(data.jqXHR) {
                                                    data.jqXHR.abort();
                                            }
                                    });
                                    var xhr = data.submit();
                                    data.context.data('data', { jqXHR: xhr });
                            }
                    },
                    progressall: function(e, data) {
                            var progress = data.loaded / data.total * 100;

                            $("[for='File'] label").html(progress.toFixed(0) + '%');
                            $("[for='File'] div.scale").css('width', progress.toFixed(0) + '%');
                    },
                    done: function(e, data) {
                            if(data.textStatus == 'success') {
                                    var fileName = data.result.fileName;
                                    var imageFile = data.result.imageFile;

                                    $('#hdnFileName').val(fileName);
                                    $("[for='File']").addClass("hide");

                                    $('#hdnCoverImageFileSelected').val("1");
                                    $('#hdnCoverImageFileName').val(imageFile);
                                    $('#imgPreview').attr("src", "/files/temp/" + imageFile);

                                    $("div.rightbar").removeClass("hidden");

                                    //auto save
                                    if(parseInt($("#ContentID").val()) > 0) {
                                            cContent.save();
                                    }
                            }
                    },
                    fail: function(e, data) {
                            $("[for='File']").addClass("hide");
                    }
            });

            //select file
            $("#FileButton").removeClass("hide").click(function(){
                    $("#File").click();
            });
        }
    };
    
    this.addImageUpload = function() {
        if($("html").hasClass("lt-ie10")) {
            $("#CoverImageFile").uploadify({
                    'swf': '/uploadify/uploadify.swf',
                    'uploader': '/' + $('#currentlanguage').val() + '/' + route["contents_uploadcoverimage2"],
                    'cancelImg': '/uploadify/uploadify-cancel.png',
                    'fileTypeDesc': 'Image Files',
                    'fileTypeExts': '*.jpg;*.png;*.gif;*.jpeg',
                    'buttonText': "Choose Image...",
                    'multi': false,
                    'auto': true,
                    'successTimeout': 300,
                    'onSelect': function (file) {
                            $('#hdnCoverImageFileSelected').val("1");
                            $("[for='CoverImageFile']").removeClass("hide");
                    },
                    'onUploadProgress': function(file, bytesUploaded, bytesTotal, totalBytesUploaded, totalBytesTotal) {
                            var progress = totalBytesUploaded / totalBytesTotal * 100;
                            $("[for='CoverImageFile'] label").html(progress.toFixed(0) + '%');
                            $("[for='CoverImageFile'] div.scale").css('width', progress.toFixed(0) + '%');
                    },
                    'onUploadSuccess': function (file, data, response) {

                            if(data.getValue("success") == "true") {
                                    var fileName = data.getValue("filename");

                                    $('#hdnCoverImageFileName').val(fileName);
                                    $('#imgPreview').attr("src", "/files/temp/" + fileName);
                                    $("[for='CoverImageFile']").addClass("hide");

                                    //auto save
                                    if(parseInt($("#ContentID").val()) > 0) {
                                            cContent.save();
                                    }
                            }
                    },
                    'onCancel': function(file) {
                            $("[for='CoverImageFile']").addClass("hide");
                    }
            });
        } else {
            $("#CoverImageFile").fileupload({
                    url: '/' + $('#currentlanguage').val() + '/' + route["contents_uploadcoverimage"],
                    dataType: 'json',
                    sequentialUploads: true,
                    formData: { 
                            'element': 'CoverImageFile'
                    },
                    add: function(e, data) {
                            if(/\.(gif|jpg|jpeg|tiff|png)$/i.test(data.files[0].name)) {
                                    $('#hdnCoverImageFileSelected').val("1");
                                    $("[for='CoverImageFile']").removeClass("hide");

                                    data.context = $("[for='CoverImageFile']");
                                    data.context.find('a').click(function(e){
                                            e.preventDefault();
                                            var template = $("[for='CoverImageFile']");
                                            data = template.data('data') || {};
                                            if(data.jqXHR)
                                            {
                                                    data.jqXHR.abort();
                                            }
                                    });
                                    var xhr = data.submit();
                                    data.context.data('data', { jqXHR: xhr });
                            }
                    },
                    progressall: function(e, data) {
                            var progress = data.loaded / data.total * 100;

                            $("[for='CoverImageFile'] label").html(progress.toFixed(0) + '%');
                            $("[for='CoverImageFile'] div.scale").css('width', progress.toFixed(0) + '%');
                    },
                    done: function(e, data) {
                            if(data.textStatus == 'success')
                            {
                                    //var fileName = data.result['CoverImageFile'][0].name;
                                    var fileName = data.result.fileName;

                                    $('#hdnCoverImageFileName').val(fileName);
                                    $('#imgPreview').attr("src", "/files/temp/" + fileName);
                                    $("[for='CoverImageFile']").addClass("hide");

                                    //auto save
                                    if(parseInt($("#ContentID").val()) > 0) {
                                            cContent.save();
                                    }
                            }
                    },
                    fail: function(e, data) {
                            $("[for='CoverImageFile']").addClass("hide");
                    }
            });

            //select file
            $("#CoverImageFileButton").removeClass("hide").click(function(){
                    $("#CoverImageFile").click();
            });
        }
    };
};

///////////////////////////////////////////////////////////////////////////////////////
// ORDER
var cOrder = new function () {

    this.objectName = "orders";

    this.doRequest = function (t, u, d, funcError) {
        return cAjax.doSyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcError);
    };

    this.doAsyncRequest = function (t, u, d, funcSuccess, funcError) {
        cAjax.doAsyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcSuccess, funcError, true);
    };

    this.save = function () {

        cCommon.save(this.objectName);
    };

    this.erase = function () {
        cCommon.erase(this.objectName);
    };
};

///////////////////////////////////////////////////////////////////////////////////////
// REPORT
var cReport = new function () {

    this.objectName = "reports";

    this.doRequest = function (t, u, d, funcError) {
        return cAjax.doSyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcError);
    };

    this.doAsyncRequest = function (t, u, d, funcSuccess, funcError) {
        cAjax.doAsyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcSuccess, funcError, true);
    };

    this.getParameters = function () {
        var param = "";
        param = param + "&sd=" + $("#start-date").val();
        param = param + "&ed=" + $("#end-date").val();
        param = param + "&customerID=" + $("#ddlCustomer").val();
        param = param + "&applicationID=" + $("#ddlApplication").val();
        param = param + "&contentID=" + $("#ddlContent").val();
        param = param + "&country=" + $("#ddlCountry").val();
        param = param + "&city=" + $("#ddlCity").val();
        param = param + "&district=" + $("#ddlDistrict").val();
        return param;
    };

    this.CountryOnChange = function (obj) {
        cReport.loadCityOptionList();
    };

    this.CityOnChange = function (obj) {
        cReport.loadDistrictOptionList();
    };

    this.loadCountryOptionList = function () {
        var t = 'GET';
        var u = '/' + $('#currentlanguage').val() + '/' + route["reports_location_country"];
        var d = "customerID=" + $("#ddlCustomer").val() + "&applicationID=" + $("#ddlApplication").val() + "&contentID=" + $("#ddlContent").val();
        cReport.doAsyncRequest(t, u, d, function (ret) {
            $("#ddlCountry").html(ret);
            $('#ddlCountry').change();
            $('#ddlCountry').trigger('chosen:updated');
        });
    };

    this.loadCityOptionList = function () {
        var t = 'GET';
        var u = '/' + $('#currentlanguage').val() + '/' + route["reports_location_city"];
        var d = "customerID=" + $("#ddlCustomer").val() + "&applicationID=" + $("#ddlApplication").val() + "&contentID=" + $("#ddlContent").val() + "&country=" + $("#ddlCountry").val();
        cReport.doAsyncRequest(t, u, d, function (ret) {
            $("#ddlCity").html(ret);
            $('#ddlCity').change();
            $('#ddlCity').trigger('chosen:updated');
        });
    };

    this.loadDistrictOptionList = function () {
        var t = 'GET';
        var u = '/' + $('#currentlanguage').val() + '/' + route["reports_location_district"];
        var d = "customerID=" + $("#ddlCustomer").val() + "&applicationID=" + $("#ddlApplication").val() + "&contentID=" + $("#ddlContent").val() + "&country=" + $("#ddlCountry").val() + "&city=" + $("#ddlCity").val();
        cReport.doAsyncRequest(t, u, d, function (ret) {
            $("#ddlDistrict").html(ret);
            $('#ddlDistrict').trigger('chosen:updated');
        });
    };

    this.OnChange = function (obj) {
        var param = this.getParameters();

        $("a.report-button").each(function () {

            var baseAddress = $(this).attr("baseAddress");

            $(this).attr("href", baseAddress + param);
        });
    };

    this.refreshReport = function () {
        var param = this.getParameters();
        var url = "/" + $('#currentlanguage').val() + "/" + route["reports"] + "/" + $("#report").val() + "?dummy=1" + param;
        this.setIframeUrl(url);
        cNotification.loader();
    };

    this.downloadAsExcel = function () {
        var param = this.getParameters();
        window.open("/" + $('#currentlanguage').val() + "/" + route["reports"] + "/" + $("#report").val() + "?xls=1" + param);
    };

    this.viewOnMap = function () {
        var param = this.getParameters();
        var url = "/" + $('#currentlanguage').val() + "/" + route["reports"] + "/" + $("#report").val() + "?map=1" + param;
        this.setIframeUrl(url);
    };

    this.setIframeUrl = function (src) {
        $("iframe").load(function () {
            var h = $(this).contents().find('body').height() + 30;
            $(this).height(h);
            cNotification.element.removeClass('statusbar-loader');
        }).attr("src", src);
    };
};

///////////////////////////////////////////////////////////////////////////////////////
// COMMON
var cCommon = new function () {
    this.doRequest = function (t, u, d, funcError) {
        return cAjax.doSyncRequest(t, u, d, funcError);
    };

    this.doAsyncRequest = function (t, u, d, funcSuccess, funcError) {
        cAjax.doAsyncRequest(t, u, d, funcSuccess, funcError, true);
    };

    this.save = function (param, fSuccess, formID) {
        if (typeof fSuccess !== 'function') {
            fSuccess = function (ret) {
                cNotification.success();
                var qs = cCommon.getQS();
            };
        }

        cNotification.hide();
        var frm = null;
        if (typeof formID !== 'undefined') {
            frm = $("#" + formID);
        } else {
            frm = $("form:first");
        }
        var validate = cForm.validate(frm);
        if (validate) {
            cNotification.loader();

            var t = 'POST';
            var u = '/' + $('#currentlanguage').val() + '/' + route[param + "_save"];
            var d = cForm.serialize(frm);
            cCommon.doAsyncRequest(t, u, d, fSuccess);
        } else {
            cNotification.validation();
        }
    };

    this.erase = function (param) {
        cNotification.hide();
        cNotification.loader();

        var frm = $("form:first");
        var t = 'POST';
        var u = '/' + $('#currentlanguage').val() + '/' + route[param + "_delete"];
        var d = cForm.serialize(frm);
        cCommon.doAsyncRequest(t, u, d, function (ret) {
            cNotification.success();
            var qs = cCommon.getQS();
            document.location.href = '/' + $('#currentlanguage').val() + '/' + route[param] + qs;
        });
    };

    this.getQS = function () {
        var qs = "";
        var customerID = "";
        var applicationID = "";
        var url = document.location.href;

        //kullanici ve uygulama listesinde customerID olabilir
        if (url.indexOf(route["users"]) > -1 || url.indexOf(route["applications"]) > -1)
        {
            customerID = getParameterByName("customerID");
            if (customerID.length > 0) {
                qs = qs + (qs.length > 0 ? "&" : "?") + "customerID=" + customerID;
            } else {
                customerID = $("#CustomerID").val();
                if (customerID !== undefined && customerID.length > 0) {
                    qs = qs + (qs.length > 0 ? "&" : "?") + "customerID=" + customerID;
                }
            }
        }

        //icerik listesinde applicationID olabilir
        if (url.indexOf(route["contents"]) > -1)
        {
            applicationID = getParameterByName("applicationID");
            if (applicationID.length > 0) {
                qs = qs + (qs.length > 0 ? "&" : "?") + "applicationID=" + applicationID;
            } else {
                applicationID = $("#ApplicationID").val();
                if (applicationID !== undefined && applicationID.length > 0) {
                    qs = qs + (qs.length > 0 ? "&" : "?") + "applicationID=" + applicationID;
                }
            }
        }
        return qs;
    };
};

///////////////////////////////////////////////////////////////////////////////////////
// NOTIFICATION
var cNotification = new function () {
    var _self = this;

    this.element = null;

    $(function () {
        _self.element = $("#myNotification");
    });

    this.validation = function (text, detail) {
        text = text ? text : notification["validation"];
        this.setClass("statusbar-warning");
        this.element.find("div.statusbar-icon span").removeAttr("class").addClass("icon-warning-sign");
        this.hideTexts(text, detail);
        this.show();
    };

    this.info = function (text, detail) {
        this.setClass("statusbar-info");
        this.element.find("div.statusbar-icon span").removeAttr("class").addClass("icon-info");
        this.hideTexts(text, detail);
        this.show();
    };

    this.loader = function (text, detail) {
        text = text ? text : notification["loading"];
        this.setClass("statusbar-loader");
        this.element.find("div.statusbar-icon span").removeAttr("class");
        this.hideTexts(text, detail);
        this.show();

        if (this.element.find('#galeSpinner').length == 0) {
            this.element.prepend("<img src='data:image/png;base64,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' id='galeSpinner' style='position:absolute;left:10px;'>");
        }
        this.element.find('#galeSpinner').removeClass();
        this.element.find('#galeSpinner').hide();
        this.element.find('#galeSpinner').show('scale', {percent: 100}, 600);

        function animateRotate(start, end, duration, easingEffect) {
            var object = _self.element.find('#galeSpinner');
            $({deg: start}).animate({deg: end}, {
                duration: duration,
                easing: easingEffect,
                step: function (now) {
                    object.css({
                        '-webkit-transform': "rotate(" + now + "deg)"
                    }).css({
                        '-moz-transform': "rotate(" + now + "deg)"
                    }).css({
                        '-ms-transform': "rotate(" + now + "deg)"
                    }).css({
                        'transform': "rotate(" + now + "deg)"
                    });
                },
                complete: function () {
                    if (_self.element.hasClass("statusbar-loader"))
                    {
                        animateRotate(0, 1800, 900, 'linear');
                    }
                    else if (!object.hasClass('stopAnimeOne')) {
                        animateRotate(0, 720, 1000, 'easeOutQuint');
                        object.addClass('stopAnimeOne');
                        if (!_self.element.hasClass("statusbar-danger")) {
                            setTimeout(function () {
                                _self.hide(500);
                            }, 1500);
                        }
                    }
                }
            });
        }
        animateRotate(0, 1800, 1200, 'easeInCubic');
    };

    this.success = function (text, detail) {
        text = text ? text : notification["success"];
        this.setClass("statusbar-success");
        this.element.find("div.statusbar-icon span").removeAttr("class").addClass("icon-ok");
        this.hideTexts(text, detail);
        this.show();
    };

    this.failure = function (text, detail) {
        text = text ? text : notification["failure"];
        this.setClass("statusbar-danger");
        this.element.find("div.statusbar-icon span").removeAttr("class").addClass("icon-remove");
        this.hideTexts(text, detail);
        this.show();
    };

    this.show = function () {
        if (!(this.element.hasClass("statusbar-loader") || this.element.hasClass("statusbar-success") || this.element.hasClass("statusbar-danger")) && this.element.find('#galeSpinner').length > 0) {
            this.element.find("#galeSpinner").remove();
        }
        this.hide();
        this.element.show();
    };

    this.hide = function (v) {
        v = v ? v : 0;
        this.element.hide(v);
    };

    this.setClass = function (c) {
        this.element.removeAttr("class").addClass("statusbar").addClass(c);
    };

    this.hideTexts = function (text, detail) {
        text = text ? text : '';
        detail = detail ? detail : '';
        this.element.find("div.statusbar-text span.text").html(text);
        this.element.find("div.statusbar-text span.detail").html(detail);
    };
};

///////////////////////////////////////////////////////////////////////////////////////
// AJAX
var cAjax = new function () {
    this.doSyncRequest = function (t, u, d, funcError) {
        updatePageRequestTime();
        if (typeof funcError === "undefined") {
            funcError = function (ret) {
                cNotification.failure(ret.getValue("errmsg"));
            };
        }

        return $.ajax({
            async: false,
            type: t,
            url: u,
            data: d,
            error: funcError
        }).responseText;
    };

    this.doAsyncRequest = function (t, u, d, funcSuccess, funcError, checkIfUserLoggedIn) {
        updatePageRequestTime();

        if (typeof funcError === "undefined") {
            funcError = function (ret) {
                cNotification.failure(ret.getValue("errmsg"));
            };
        }
        checkIfUserLoggedIn = (typeof checkIfUserLoggedIn === "undefined") ? true : false;
        $.ajax({
            type: t,
            url: u,
            data: d,
            success: function (ret) {
                if (t === 'GET') {
                    funcSuccess(ret);
                    return;
                }

                if (checkIfUserLoggedIn) {
                    if (ret.getValue("userLoggedIn") == "true") {
                        if (ret.getValue("success") == "true") {
                            funcSuccess(ret);
                        } else {
                            funcError(ret);
                        }
                        return;
                    }
                    cUser.go2Login();
                } else {
                    if (ret.getValue("success") == "true") {

                        funcSuccess(ret);
                    } else {
                        funcError(ret);
                    }
                }
            },
            error: funcError
        });
    };
};

///////////////////////////////////////////////////////////////////////////////////////
// FORM
var cForm = new function () {
    this.validate = function (formObj) {
        var ret = true;
        formObj.each(function () {
            $("div.error", $(this)).removeClass("error");
            $(".required", $(this)).each(function () {
                if (!$(this).val()) {
                    ret = false;
                    $(this).prev().addClass("error");
                    $(this).parent().prev().addClass("error");
                }
            });
        });
        return ret;
    };

    this.serialize = function (formObj) {
        var ret = "";
        formObj.each(function () {
            if ($(this).is("form")) {
                ret = ret + "&" + $(this).serialize();
            }
        });
        return ret;
    };
};

///////////////////////////////////////////////////////////////////////////////////////
// MODALFORM
var modalform = new function () {

    var modalForm;
    var contentContainer;

    this.show = function (c) {
        this.modalForm = c;
        this.contentContainer = $(".modalformcontainer", this.modalForm);
        this.contentContainer.html('<div class="loader"></div>');
        this.modalForm.removeClass("hidden");
        this.reposition();

        //show overlay window
        var overlay;
        if ($(".ui-widget-overlay").size() > 0) {
            overlay = $(".ui-widget-overlay");
        } else {
            $('<div></div>')
                    .addClass('ui-widget-overlay')
                    .addClass('hidden')
                    .css("z-index", "1001")
                    .appendTo("body");
            overlay = $(".ui-widget-overlay");
        }

        overlay.css("width", $(document).width())
                .css("height", $(document).height())
                .removeClass("hidden");
    };

    this.reposition = function () {
        var t = $(window).scrollTop() + (($(window).height() - this.modalForm.height()) / 2);
        var l = ($(window).width() - this.modalForm.width()) / 2;
        this.modalForm.css({
            left: l + "px",
            top: t + "px"
        });
    };

    this.content = function (c) {
        this.contentContainer.html(c);
        this.reposition();
    };

    this.close = function () {
        this.modalForm.addClass("hidden");
        $(".ui-widget-overlay").addClass("hidden");
    };
};

var cGoogleMap = new function () {
    this.objectName = "maps";
    this.doRequest = function (t, u, d, funcError) {
        return cAjax.doSyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcError);
    };

    this.save = function () {
        cCommon.save(this.objectName);
    };
};

var cTemplate = new function () {
    var background = 1;
    var foreground = 1;
    this.newbackground = 1;
    this.newforeground = 1;
    this.objectName = "contents_template";
    this.doRequest = function (t, u, d, funcError) {
        return cAjax.doSyncRequest(t, u, "obj=" + this.objectName + "&" + d, funcError);
    };

    this.save = function () {
        var fsuccess = function(ret) {
            background = $('.templateBackgroundChange:checked').val();
            foreground = $('.templateForegroundChange:checked').val();
            cNotification.success();
        };
        cCommon.save(this.objectName, fsuccess, "templateForm");
    };

    this.initialize = function(bg, fg) {
        background = bg;
        foreground = fg;

        $(".templateScreen").click(function (event) {
            if($(event.target).is('img'))
            {
                $(".templateReadScreen img").attr('src', event.target.src);
                $(".templateReadScreen .form-row p:nth-child(1)").text(event.target.nextElementSibling.children[0].innerText);
                $(".templateReadScreen .form-row p:nth-child(2)").text(event.target.nextElementSibling.children[1].innerText);
                $(".templateReadScreen .form-row p:nth-child(3)").text(event.target.nextElementSibling.children[2].innerText);
            }
        });

        $('.templateBackgroundChange').on('change', function (e) {
            $('.app-background-templates').remove();
            switch(parseInt($('.templateBackgroundChange:checked').val())) {
                case 1:
                    $('head').append('<link rel="stylesheet" class="app-background-templates" href="/css/template-chooser/background-template-dark.css" type="text/css" />');
                    break;
                case 2:
                    $('head').append('<link rel="stylesheet" class="app-background-templates" href="/css/template-chooser/background-template-light.css" type="text/css" />');
                    break;
            }
        });

        $('.templateForegroundChange').on('change', '', function (e) {
            $('.app-foreground-templates').remove();
            switch(parseInt($('.templateForegroundChange:checked').val())) {
                case 1:
                $('head').append('<link rel="stylesheet" class="app-foreground-templates" href="/css/template-chooser/foreground-template-blue.css" type="text/css" />');
                    break;
                case 2:
                $('head').append('<link rel="stylesheet" class="app-foreground-templates" href="/css/template-chooser/foreground-template-green.css" type="text/css" />');
                    break;
                case 3:
                $('head').append('<link rel="stylesheet" class="app-foreground-templates" href="/css/template-chooser/foreground-template-yellow.css" type="text/css" />');
                    break;
                case 4:
                $('head').append('<link rel="stylesheet" class="app-foreground-templates" href="/css/template-chooser/foreground-template-red.css" type="text/css" />');
                    break;
                case 5:
                $('head').append('<link rel="stylesheet" class="app-foreground-templates" href="/css/template-chooser/foreground-template-orange.css" type="text/css" />');
                    break;
            }
        });

        $('#modalTemplateChooser').on('shown.bs.modal', function () {
            $('.container.content-list').addClass('blurred');
            $('#templateChooserBox').show(500);
            $('#templateChooserBox .site-settings').addClass('active');
            $('.templateScreen .footer').css('left','0');
            $('.templateSplashScreen').removeClass('hide').fadeTo("slow", 1, function () {
                setTimeout(function () {
                    $('.templateSplashScreen').fadeTo("slow", 0, function () {
                        $('.templateSplashScreen').addClass("hide");
                        $('.templateScreen').removeClass('hide').fadeTo("slow", 1);
                    });
                }, 1000);
            });

            $(".templateScreen .container [class*='col-']").click(function () {
                $('.templateScreen').fadeTo("slow", 0.5, function () {
                    $('.templateReadScreen').removeClass('hide').fadeTo("slow", 1);
                });
            });

            $("#templateBtnRead").click(function () {
                $('.templateScreen, .templateReadScreen').fadeTo("slow", 0, function () {
                    $('.templateContentScreen').removeClass('hide').fadeTo("slow", 1);
                });
            });

            $(".footerBtnHome").click(function () {
                $('.templateContentScreen, .templateScreen, .templateReadScreen').fadeTo("slow", 0, function () {
                    $('.templateScreen, .templateReadScreen, .templateContentScreen').addClass('hide');
                    $('.templateScreen').removeClass('hide').fadeTo("slow", 1);
                });
            });
            setSelected();
            $('.templateBackgroundChange').trigger("change");
            $('.templateForegroundChange').trigger("change");
        });

        $('#modalTemplateChooser .templateScreen').click(function (event) {
            if(!$('.templateReadScreen').hasClass('hide')){
                 $('.templateContentScreen, .templateScreen, .templateReadScreen').fadeTo("slow", 0, function () {
                    $('.templateScreen, .templateReadScreen, .templateContentScreen').addClass('hide');
                    $('.templateScreen').removeClass('hide').fadeTo("slow", 1);
                });
            }
        });

        $('#modalTemplateChooser').on('hidden.bs.modal', function (e) {
            $('.templateSplashScreen, .templateScreen, .templateReadScreen, .templateContentScreen').addClass('hide');
            $('.container.content-list').removeClass('blurred');
            $('#templateChooserBox').hide(500);
            $('#templateChooserBox .site-settings').removeClass('active');
            $('.templateExtrasScreen').addClass('hide').fadeTo("fast", 0);
            $('.templateScreen').css('margin-left', '0');
            $('.templateScreen .footer').css('right', '0');
        });

        $('.templateExtrasScreen. .title-drop').click(function () {
            $(this).parent().parent().next().toggleClass('panelClose');
        });

        $('.templateContentScreen. .content-page').click(function () {
            $('.templateContentScreen .header').toggleClass('hide');
            $('.templateContentScreen .thumbnails').toggleClass('hide');
            $('.templateContentScreen .footer').toggleClass('hide');
        });

        $('#templateChooserClose').click(function () {
            $('#modalTemplateChooser').modal('hide');
        });

        $('.header-categories').click(function(){
            if($('.templateExtrasScreen').hasClass('hide')){
                $('.templateExtrasScreen').removeClass('hide').fadeTo( "fast" , 1);
                $('.templateScreen').css('margin-left','75%'); 
                $('.templateScreen .footer').css('left','75%');
            }
            else{
                $('.templateExtrasScreen').addClass('hide').fadeTo( "fast" , 0);
                $('.templateScreen').css('margin-left','0');
                $('.templateScreen .footer').css('left','0');
            }
        });

    };
    
    function setSelected () {
        var elemBackSet = $('.templateBackgroundChange');
        var elemForeSet = $('.templateForegroundChange');
        for (var i = 0; i < elemBackSet.length; i++) {
            var jQueryElem = $(elemBackSet[i]);
            if (jQueryElem.val() == background) {
                jQueryElem.parent().addClass('checked');
                jQueryElem.attr('checked', 'checked');
            } else {
                jQueryElem.removeAttr("checked");
                jQueryElem.parent().removeClass('checked');
            }
        }

        for (var j = 0; j < elemForeSet.length; j++) {
            var jQueryElem = $(elemForeSet[j]);
            if (jQueryElem.val() == foreground) {
                jQueryElem.parent().addClass('checked');
                jQueryElem.attr('checked', 'checked');
            } else {
                jQueryElem.removeAttr("checked");
                jQueryElem.parent().removeClass('checked');
            }
        }
    };
};

var cBanner = new function () {
    this.addImageUpload = function() {
        if($("html").hasClass("lt-ie10")) {
            $("#ImageFile").uploadify({
                'swf': '/uploadify/uploadify.swf',
                'uploader': '/' + $('#currentlanguage').val() + '/common/imageupload_ltie10' ,
                'cancelImg': '/uploadify/uploadify-cancel.png',
                'fileTypeDesc': 'Image Files',
                'fileTypeExts': '*.jpg;*.png;*.gif;*.jpeg',
                'buttonText': "Choose Image...",
                'multi': false,
                'auto': true,
                'successTimeout': 300,
                'onSelect': function (file) {
                    $('#hdnImageFileSelected').val("1");
                    $("[for='ImageFile']").removeClass("hide");
                },
                'onUploadProgress': function(file, bytesUploaded, bytesTotal, totalBytesUploaded, totalBytesTotal) {
                    var progress = totalBytesUploaded / totalBytesTotal * 100;
                    $("[for='ImageFile'] label").html(progress.toFixed(0) + '%');
                    $("[for='ImageFile'] div.scale").css('width', progress.toFixed(0) + '%');
                },
                'onUploadSuccess': function (file, data, response) {

                    if(data.getValue("success") == "true") {
                        var fileName = data.getValue("filename");

                        $('#hdnImageFileName').val(fileName);
                        $('#imgPreview').attr("src", "/files/temp/" + fileName);
                        $("[for='ImageFile']").addClass("hide");

                        //auto save
                        if(parseInt($("#ContentID").val()) > 0) {
                            cContent.save();
                        }
                    }
                },
                'onCancel': function(file) {
                    $("[for='ImageFile']").addClass("hide");
                }
            });
        } else {
            $("#ImageFile").fileupload({
                url: '/' + $('#currentlanguage').val() + '/common/imageupload',
                dataType: 'json',
                sequentialUploads: true,
                formData: { 
                    'element': 'ImageFile'
                },
                add: function(e, data) {
                    if(/\.(gif|jpg|jpeg|tiff|png)$/i.test(data.files[0].name)) {
                        $('#hdnImageFileSelected').val("1");
                        $("[for='ImageFile']").removeClass("hide");

                        data.context = $("[for='ImageFile']");
                        data.context.find('a').click(function(e){
                            e.preventDefault();
                            var template = $("[for='ImageFile']");
                            data = template.data('data') || {};
                            if(data.jqXHR) {
                                    data.jqXHR.abort();
                            }
                        });
                        var xhr = data.submit();
                        data.context.data('data', { jqXHR: xhr });
                    }
                },
                progressall: function(e, data) {
                    var progress = data.loaded / data.total * 100;

                    $("[for='ImageFile'] label").html(progress.toFixed(0) + '%');
                    $("[for='ImageFile'] div.scale").css('width', progress.toFixed(0) + '%');
                },
                done: function(e, data) {
                    if(data.textStatus == 'success') {
                        var fileName = data.result.fileName;
                        $('#hdnImageFileName').val(fileName);
                        $('#imgPreview').attr("src", "/files/temp/" + fileName);
                        $("[for='ImageFile']").addClass("hide");

                        //auto save
                        if(parseInt($("#ContentID").val()) > 0) {
                            cContent.save();
                        }
                    }
                },
                fail: function(e, data) {
                        $("[for='ImageFile']").addClass("hide");
                }
            });

            //select file
            $("#ImageFileButton").removeClass("hide").click(function(){
                    $("#ImageFile").click();
            });
        }
    };
    
    this.save = function () {
        cCommon.save(
	    this.objectName, 
	    function (ret) {
		primaryKeyID = ret.getValue("primaryKeyID");
		cNotification.success();
		 var goto = '/' + $('#currentlanguage').val() + '/' + route[_self.objectName] + '/' + primaryKeyID;
		 console.log(goto);
//		 document.location.href = goto;
	    }
	);
    };
};