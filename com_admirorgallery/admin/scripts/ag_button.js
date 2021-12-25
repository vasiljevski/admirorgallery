$(function() {

    $(".ag_form_folder_name").click(function(event) {
        event.preventDefault();
    });
    $('#ag_form_insertParams').change(function() {
        if ($('#ag_form_insertParams').is(':checked')) {
            $('#ag_form_params').fadeIn("slow");
        } else {
            $('#ag_form_params').fadeOut("slow");
        }
    });

});

function AG_createTriggerCode(name) {

    var ag_params = "";

    var input_fields = $(".paramlist_value input, .paramlist_value select").serializeArray();
    $.each(input_fields, function(index, field) {
        ag_params += " " + field.name.substring(7, (field.name.length - 1)) + '="' + field.value + '"';
    });
    if ($('#ag_form_insertParams').is(':checked')) {
        var code = '{AG' + ag_params + '}' + $('select[name="ag_form_folder_name"]').val() + '{/AG}';
    } else {
        var code = '{AG}' + $('select[name="ag_form_folder_name"]').val() + '{/AG}';
    }
    if (isJoomla4(name)) {
        window.parent.Joomla.editors.instances[name].replaceSelection(code);
    } else {
        jInsertEditorText(code, name);
    }
    closeAdmirorButton(name);
}

function closeAdmirorButton(name) {
    if (isJoomla4(name)) {
        window.parent.Joomla.Modal.getCurrent().close();
    } else {
        window.parent.SqueezeBox.close();
    }
}

function isJoomla4(name) {
    return (window.parent.Joomla &&
        window.parent.Joomla.editors &&
        window.parent.Joomla.editors.instances &&
        window.parent.Joomla.editors.instances.hasOwnProperty(name));
}