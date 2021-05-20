jQuery(document).ready(function($){
    console.log("jquery ready")

    // limit for automatically switch to serverSide
    const limitRowsForClient = 10;

    // for switch client or serverSide DataTable
    let server = false;

    console.log("displaying refmap");
    console.log(refMap);


    // check if the client don't call table of getBalance
    if(refMap != 'null' && table != 'null'){

        const references = refMap.replace('[', '').replace(']', '');
        const refColumns = references.split(',');
    
        let columnsArray = [];
        
        // create array with all columns, from refMap
        refColumns.forEach(element => {
            str = element.replace(/^"(.*)"$/, '$1');
            if(str != 'creationTimestamp'){
                columnsArray.push({ title: str, data: str })
            }
        });
    
        // Ajax for have count of db table and determine client or server side
        $.ajax({
            url: '/admin/dbview/count/' + table,
            type: 'get',
            beforeSend:function(){
                $('.spinner-grow').show();
            },
            success: function(response){
                $('.spinner-grow').hide()
                // if count(response) bigger than limit, table switch on server side
                if(response > limitRowsForClient){
                    ajaxServerSide();
                    server = true;
                }else{
                    ajaxClientSide();
                    server = false;
                }
            },
            error: function (jqXHR, exception){
    
                $msg = '';
    
                if (jqXHR.status === 0) {
                    msg = 'Not connect.\n Verify Network.';
                } else if (jqXHR.status == 404) {
                    msg = 'Requested page not found. [404]';
                } else if (jqXHR.status == 500) {
                    msg = 'Internal Server Error [500].';
                } else if (exception === 'timeout') {
                    msg = 'Time out error.';
                } else if (exception === 'abort') {
                    msg = 'Ajax request aborted.';
                } else {
                    msg = 'Uncaught Error.\n' + jqXHR.responseText;
                }
                $('#jsonAlert').slideDown(500).html($msg);
            }
        })
    
    
        // Manually switch client or server side
        $('.switchTables').on('click', (e)=>{
    
            const buttonId = $(e.currentTarget).attr('id');
    
            if(buttonId == 'serverSide' && server === false){
                createNewTable();
                ajaxServerSide();
                server = true;
    
            }else if(buttonId == 'clientSide' && server === true){
                createNewTable();
                ajaxClientSide();
                server = false;
            }
        })


        // reinitialize the <table> for creating a new
        function createNewTable(){
            $('#factoryTable').remove();
            $('#tableContainer').html('<table class="text-light table table-dark" id="factoryTable"><thead></thead><tbody></tbody></table>');
        }

        // switch to server side
        function ajaxServerSide(){

            $('#factoryTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '/admin/dbview/' + table,
                columns: columnsArray,
            })
        }

        // switch to client side
        function ajaxClientSide(){

            $('#factoryTable').DataTable({
                ajax: '/admin/dbview/' + table,
                columns: columnsArray,
            })
        }


    }

    

})