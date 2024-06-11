jQuery(document).ready(function($) {
    let table = new DataTable('#tbl-contact-table');
});

jQuery(document).ready(function($) {
    $("#btn_sms_form").on("click", function(event) {
        event.preventDefault();

        var formData = $("#frm_sms_form").serialize() + "&action=sms_ajax_handler&param=save_form";


        $.ajax({
            url: sms_ajax_url,
            data: formData,
            method: "POST",
            success: function(response) {
                var data=jQuery.parseJSON(response);
                if(data.status)
                    {
                        toastr.success(data.message);
                        setTimeout(function(){
                            location.reload()
                        },2000);
                    }
                else
                {
                    toastr.error(data.message);
                    setTimeout(function(){
                        location.reload()
                    },2000);
                }
            },
            error: function(response) {
                // Handle error response
            }
        });
    });
    
    

        load_contact();
    
        
    
});

function load_contact() {
    var formData = "&action=sms_ajax_handler&param=load_contact";
    var contactHTML = "";

    // Destroy existing DataTable instance
    if (jQuery.fn.DataTable.isDataTable("#tbl-contact-table")) {
        jQuery("#tbl-contact-table").DataTable().destroy();
    }

    jQuery.ajax({
        url: sms_ajax_url,
        data: formData,
        method: "GET",
        success: function(response) {
            console.log(response);  // Add logging to see the raw response
            var data = jQuery.parseJSON(response);
            if (data.status) {
                jQuery.each(data.data, function(index, contact) {
                    contactHTML += "<tr>";
                    contactHTML += "<td>" + contact.id + "</td>";
                    contactHTML += "<td>" + contact.name + "</td>";
                    contactHTML += "<td>" + contact.email + "</td>";
                    contactHTML += "<td>" + contact.gender + "</td>";
                    contactHTML += "<td>" + contact.phone_no + "</td>";
                    contactHTML += '<td><a href="admin.php?page=contact-management&action=edit&id='+contact.id+'" class="btn-edit">Edit</a> <a href="admin.php?page=contact-management&action=view&id='+contact.id+'" class="btn-view">View</a> <a class="btn-delete btn-contact-delete" data-id="'+contact.id+'">Delete</a></td>';
                    contactHTML += "</tr>";
                });
                jQuery("#tbl-contact-table tbody").html(contactHTML);  // Use tbody to append rows

                // Initialize DataTable after populating the table
                jQuery("#tbl-contact-table").DataTable();
            }
        },
        error: function(error) {
            console.error(error);  // Add error logging to debug failed AJAX requests
        }
    });
}

jQuery(document).ready(function() {
    load_contact();
});

jQuery(document).on("click",".btn-contact-delete",function(){
    if(confirm("Are you sure want to delete"))
        {
            var contact_id= jQuery(this).attr("data-id");
    var formData = "&action=sms_ajax_handler&param=delete_contact&contact_id="+contact_id;
    jQuery.ajax({
        url: sms_ajax_url,
        data: formData,
        method: "POST",
        success: function(response)
        {
            var data=jQuery.parseJSON(response);
            toastr.success(data.message);
                        setTimeout(function(){
                            location.reload()
                        },1000);

        },
        error: function()
        {

        }
    });
        }
});

// jQuery(document).ready(function() {
//     load_contact();
// });


