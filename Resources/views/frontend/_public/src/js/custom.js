(function ($, window) {
    // tracking part
    $('.post--load-tracking').on('click', function () {
        var button = $(this);
        var wrapper = button.data('collapsetarget');
        if (!$(wrapper).is(':visible')) {
            if ($(wrapper).find('.post--tracking-link').length > 0) {
                $(wrapper + ' .post--tracking-link:first').click();
            }
        }
    });

    $('.post--tracking-link, .post--tracking-link-header').on('click', function () {
        var button = $(this);
        var wrapper = button.data('tracking-target');
        var parent = button.data('tracking-parent');

        $(parent + ' .post--tracking-loader').show();

        $.ajax({
            url: button.data('url'),
            data: {trackingcode: button.data('tracking-code')},
            success: function (result) {
                $(parent + ' .post--tracking-loader').hide();

                if (result.noTrackingData) {
                    $(parent + ' ' + wrapper + ' .post--tracking-no-data').show();
                    $(parent + ' ' + wrapper + ' .post--tracking-tracking-data').hide();
                    return;
                }
                $(parent + ' ' + wrapper + ' .post--tracking-no-data').hide();
                $(parent + ' ' + wrapper + ' .post--tracking-tracking-data').show();

                var homeShopUrl = button.closest('.post--tracking-details').data('shop-url');

                for (var i = 0; i < result.parcels.length; i++) {
                    // create tabs
                    var parcelEvents = result.parcels[i].parcelEvents;

                    for (var j = 0; j < parcelEvents.length; j++) {
                        var parcelEvent = parcelEvents[j];
                        var container = $(parent + ' ' + wrapper + ' .post--tracking-tracking-data');
                        if (j === 0) {
                            container.html('');
                        }
                        var date = new Date(parcelEvent.eventTimestamp);
                        date = date.getDate() + '.' + ("0" + (date.getMonth() + 1)).slice(-2) + '.' + date.getFullYear() + ' ' + ("0" + (date.getHours())).slice(-2) + ':' + ("0" + (date.getMinutes())).slice(-2) + ':' + ("0" + (date.getSeconds())).slice(-2);
                        var eventDescription = parcelEvent.parcelEventReasonDescription;
                        var eventZip = parcelEvent.eventPostalCode;

                        if (j === (parcelEvents.length - 1)) {
                            // update header info element
                            var headerText = ' <div class="tracking-cell-head">' +
                                '<span class="tracking-red-notice-text">' + eventDescription + '</span><br>' +
                                '<span class="tracking-black-text">Zeitpunkt: </span>' +
                                '<span class="tracking-grey-text">' + date + '<span><br>' +
                                '</div>';

                            var titleLine = '<div class="tracking-cell tracking-cell-text"><div class="tracking-cell-title"><span class="tracking-title">Sendungsverlauf: ' + result.parcels[i].identCode + '</span></div></div>';
                            container.prepend(titleLine);
                            container.prepend(headerText);
                        }
                        var trackingCell = $('<div></div>');
                        trackingCell.addClass('tracking-cell');
                        trackingCell.append('<div class="tracking-float-left tracking-cell-icon"><img src="' + homeShopUrl + parcelEvent.icon + '"></div>');
                        trackingCell.append('<div class="tracking-float-left tracking-cell-text">Datum: <span id="tracking_delivery_date" class="tracking-date-text">' + date + '</span><br><span>' + eventDescription + ':  ' + eventZip + '</span></div>');
                        container.append(trackingCell);
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {

            }
        });
    });

    $(function () {

        $(document).ready(function () {
            getUserBranch();
        });

        function getUserBranch() {
            var subsidiary_getbranch_url = $("#subsidiary_ajax_url3").val();
            $.ajax({
                type: "POST",
                url: subsidiary_getbranch_url,
                success: function (result) {
                    $("#selected_subsidiary_wrapper").html(result);
                    $("#subsidiary_alert").html("");
                }
            });
        }

        function setUserBranch(selectedBranch, branchAddress, additionalBranchData) {
            var subsidiary_branch_url = $("#subsidiary_ajax_url2").val();
            $.ajax({
                type: "POST",
                url: subsidiary_branch_url,
                data: {
                    sBranch: selectedBranch,
                    branchAddress: branchAddress,
                    additionalBranchData: additionalBranchData
                },
                success: function () {
                    getUserBranch();
                }
            });
        }

        $("#selected_subsidiary_wrapper").on('click', '#delete_selected_user_branch', function () {
            var subsidiary_removebranch_url = $("#subsidiary_ajax_url4").val();
            $.ajax({
                type: "POST",
                url: subsidiary_removebranch_url,
                success: function () {
                    getUserBranch();
                }
            });
        });

        var cache = {};
        $("#branches").autocomplete({
            classes: {
                "ui-autocomplete": "post--branches-list"
            },
            source: function (request, response) {
                $('#branches').removeClass('has--error');
                $('.post--label-error').addClass('is--hidden');

                // cache result because of focus
                try {
                    var term = parseInt(request.term);
                } catch (err) {
                    return;
                }

                if (term in cache) {
                    if (typeof cache[term] != "undefined" && cache[term] != null && cache[term].length != null && cache[term].length > 0) {
                        response(cache[term]);
                    } else {
                        $('#branches').addClass('has--error');
                        $('.post--label-error').removeClass('is--hidden');
                        response([]);
                    }
                    return;
                }
                var branchMapping = {
                    PostOffice: 'Post-Geschäftsstelle',
                    ParcelLocker: 'Abholstation',
                    HPS: 'Hermes PaketShop'
                };
                $.ajax({
                    url: $('#subsidiary_ajax_url').val(),
                    dataType: "json",
                    data: {
                        sString: request.term
                    },
                    success: function (data) {
                        cache[term] = [];
                        response($.map(data, function (item) {
                            if (!item.errorMessage) {
                                var result = {
                                    cacheKey: term,
                                    value: item.title,
                                    label: branchMapping[item.type] + ': ' + item.title,
                                    object: item
                                };
                                cache[term].push(result);
                                return result;
                            } else {
                                $('#branches').addClass('has--error');
                                $('.post--label-error').removeClass('is--hidden');
                            }
                        }));
                    }
                });
            },
            minLength: 4,
            select: function (event, ui) {
                setUserBranch(ui.item.label, ui.item.object.address, ui.item.object.additionalBranchData);
            },
            close: function () {
                $(this).blur();
            }
        }).bind('focus', function () {
            $(this).val('');
        }).addClass('post--branches-list');
    });

    $(document).ready(function () {
        resetAclReturnInitialState();
        $('.acl-row--product .acl-return-checkbox').on('change', function () {
            handleAclReturnButtonVisibility($(this), 'checked');
        });

        $('.acl-row--product .acl-return-reason').on('change', function () {
            handleAclReturnButtonVisibility($(this), 'selected');
        });

        var postReturnUrl = $('.postReturnUrl').data('url');

        $('.acl-return-button').on('click', function () {
            if (!$(this).hasClass('is--disabled')) {
                $.loadingIndicator.open();
                var serializedParams = $('.postReturnArticles--container').find('input,select').serialize();
                // trigger ajax return
                $.ajax({
                    url: postReturnUrl,
                    data: serializedParams,
                    success: function (result) {
                        $.loadingIndicator.close();
                        if (!result.success) {
                            var template = createPostTemplate(result.errorMessage);
                            $.modal.open(template, {
                                title: 'Fehler',
                                sizing: 'content',
                                onClose: checkCurrentLabelState()
                            });
                            return;
                        }

                        var template = createPostTemplate(result.errorMessage, result.data.labelId);
                        $.modal.open(template, {
                            title: 'Retourenlabel',
                            sizing: 'content',
                            onClose: checkCurrentLabelState()
                        });
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        $.loadingIndicator.close();
                    }
                });
            }
        });
    });


    function createPostTemplate(errorMessage, labelId) {

        return $('<div>', {
            'class': 'post--label-modal',
            'html': [
                createErrorMessage(errorMessage),
                createPdfLink(labelId)
            ],
            'data-labelid': labelId ? labelId : ''
        });
    }

    function createPdfLink(labelId) {
        if (!labelId) {
            return '';
        }
        return $('<a>', {
            'rel': 'nofollow',
            'class': 'post--label-donwload-link',
            'title': 'Retoruenlabel',
            'href': $('.postFileOpenUrl').data('url') + '?labelId=' + labelId,
            'html': 'Retoruenlabel runterladen'
        });
    }

    function createErrorMessage(errorMessage) {
        if (!errorMessage) {
            return '';
        }
        return $('<div>', {
            'class': 'post--label-error',
            'html': [
                errorMessage
            ]
        });
    }

// was there error
    function checkCurrentLabelState() {
    }

    function handleAclReturnButtonVisibility(element, propName) {
        var propertyExists = false;
        if (propName == 'checked') {
            if (element.prop(propName) === true)
                propertyExists = true;
        } else if (propName == 'selected') {
            propertyExists = true;
            removeSelectError(element);
        }

        if (propertyExists) {
            if (propName == 'checked') {
                enableAclReturnSelect(element);
            }
            if (isAclProductRowValid(element)) {
                enableAclReturnRow(element.closest('.acl-row--product'));
            }
        } else {
            if (propName == 'checked') {
                disableAclReturnSelect(element);
            }
            disableAclReturnRow(element.closest('.acl-row--product'));

            if ($('.acl-row--product.acl-row-invalid').length == $('.acl-row--product').length) {
                hideAclReturn();
            }
        }
// 	// check if all checked rows are valid!
        if ($('.acl-return-checkbox:checked').closest('.acl-row--product.acl-row-invalid').length > 0 || $('.acl-return-checkbox:checked').length == 0) {
            hideAclReturn()
        } else {
            showAclReturn()
        }
    }

    function isAclProductRowValid(element) {
        var parent = element.closest('.acl-row--product');

        if (parent.find('.acl-return-checkbox').prop('checked') === false) {
            return false;
        }

        if (!parent.find('.acl-return-reason').val()) {
            return false;
        }
        return true;
    }

    function enableAclReturnSelect(element) {
        element.closest('.acl-row--product').find('select').prop('disabled', false);
        element.closest('.acl-row--product').find('select').removeClass('is--disabled');
    }

    function disableAclReturnSelect(element) {
        element.closest('.acl-row--product').find('select').prop('disabled', true);
        element.closest('.acl-row--product').find('select').addClass('is--disabled');
    }

    function enableAclReturnRow(element) {
        element.removeClass('acl-row-invalid');
    }

    function disableAclReturnRow(element) {
        element.addClass('acl-row-invalid');
    }

    function hideAclReturn() {
        $('.acl-return-button').addClass('is--disabled');
    }

    function showAclReturn() {
        $('.acl-return-button').removeClass('is--disabled');
    }

    function removeSelectError(element) {
        element.removeClass('has--error');
        element.closest('.js--fancy-select').removeClass('has--error');
    }

    function resetAclReturnInitialState() {
        $('.acl-row--product').each(function (index) {
            $(this).find('.acl-return-checkbox').prop('checked', false);
            $(this).find('.acl-return-reason').val('');
        });

        $('.acl-return-button').each(function () {
            $(this).on('click', function (e) {
                if ($(this).hasClass('is--disabled')) {
                    e.preventDefault();
                }
            });
        });
    }

    window.resetShippingToPlc = function resetShippingToPlc() {
        var extraData = {
            sessionKey: 'checkoutShippingAddressId',
            setDefaultBillingAddress: "",
            setDefaultShippingAddress: ""
        };
        $.loadingIndicator.open();

        $.ajax({
            'url': window.controller['ajax_address_selection'],
            'data': {
                id: "checkoutShippingAddressId",
                extraData: extraData
            },
            'success': function (data) {

                var parsed = $.parseHTML(data);
                var element = $(parsed).find(".address-manager--selection-form:first :input[name='id']");
                var addressiId = element.val();

                $.ajax({
                    'url': '/address/handleExtra',
                    type: "POST",
                    'data': {
                        id: addressiId,
                        extraData: extraData
                    },
                    'success': function (data) {
                        $.loadingIndicator.close(function () {
                            location.reload();
                        });
                    }
                });

            }
        });
    }

})(jQuery, window);
