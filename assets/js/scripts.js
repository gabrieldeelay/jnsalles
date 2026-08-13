

function new_place_order(id,ref){
    $('#overlay').fadeIn(300);
      $.ajax({
        url:_base_url_+'class/Mister.php?action=place_order_process',
        method:'POST',
        data:{ref: '', product_id: parseInt(id)},
        dataType:'json',
        error:err=>{
          console.error(err)          
       },
       success:function(resp){
        console.log(resp);
        if(resp.status == 'success'){ 
            location.replace(resp.redirect)
           } else if (resp.status == 'pay2m') {
           alert(resp.error);
           location.replace(resp.redirect)
         } else{
             alert(resp.error);
             location.reload();
          }
      } 
      })
}

$(document).ready(function() {
    var current_page = window.location.href;
    var page = current_page.split('/')[3];
    var slug = current_page.split('/')[4]; 
    
    if(page == 'campanha'){
        var dataid = $('#place_order').attr('data-id');
        
        
var new_button = '<button id="new_place_order" data-id="'+dataid+'" class="btn btn-success w-100 mb-2">Concluir <i class="bi bi-arrow-right-circle"></i></button>';

        $('#place_order').after(new_button);
        $('#place_order').remove();

              


        $('#new_place_order').click(function(e){
            e.preventDefault();
            var ref = $(this).attr('data-id');
            

            $.ajax({
                url: _base_url_ + 'class/Mister.php?action=get_product_by_slug',
                type: 'POST',
                data: { slug:slug},

                success: function(response) {
                    

                    var res = JSON.parse(response);

                    if (res.status == 'success') {
                        new_place_order(res.product.id,ref);
                    } else {
                        alert(res.error);
                    }
                },
                error: function() {
                    console.error('Erro ao buscar configurações!');
                }
            });
            

            


         })

    }

});