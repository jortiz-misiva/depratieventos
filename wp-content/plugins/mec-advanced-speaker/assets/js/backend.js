jQuery(document).ready(function()
{
        if (mec_enable_link_section_title==1){
            jQuery('.mec_select_speaker_type_link').show();
        }else {
            jQuery('.mec_select_speaker_type_link').hide();
        }
});
function mec_enable_link_section_title_changed(el)
{
    jQuery('.mec_select_speaker_type_link').toggle();
}

