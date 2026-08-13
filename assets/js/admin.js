$(document).ready(function() {
    return;
    var current_page = window.location.href;
    var page = current_page.split('=')[1];
    var page_edit = current_page.split('=')[2];
if (page.includes('manage_product')) {
    
   
    
        var tab_content = $('#tab7');
        var new_component = 
        '<label class="block mt-4 text-sm"> <span class="text-gray-700 dark:text-gray-400">Habilitar bloqueio de cotas?</span></label>' +
 '<div class="can-toggle">' +
						'<input type="checkbox" name="status_auto_cota" id="status_auto_cota"> ' +
					'	<label for="status_auto_cota"> ' +
						'	<div class="can-toggle__switch" data-checked="Sim" data-unchecked="Não"></div> ' +
						'</label>' +
					'</div> ' +
                   ' <div class="block glass mt-4 text-sm status_cotas"> ' +
                   '                 <span> ' +
                   '                  Cotas bloqueadas ' +
                   '                 </span> ' +
                   '                    <p id="mensagem_porcentagem" style="color:orange" class="italic  text-xs"> ' +
                   '                       Apague a cota que deseja liberar para venda  ' +
                   '                    </p> ' +
                   
                   '                     <input name="tipo_auto_cota" id="tipo_auto_cota" class="block w-full mt-1 text-sm dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input" placeholder="ex: 12345,83474,78347" value="01,02,03,04,05,06,07,08,09,10,11,12">  ' +
                   '                 </div>  ' +



                 
 '<div class="can-toggle">' +
						'<input type="checkbox" name="quantidade_auto_cota" id="quantidade_auto_cota"> ' +
					'	<label for="quantidade_auto_cota"> ' +
					
						'</label>' +
					'</div> ' 
                  
                   ;
                    if (page.includes('manage_product')) { 
                        tab_content.append(new_component);

                        var product_id = page_edit;

                        if(product_id){
                            $.ajax({
                                url: _base_url_ + 'class/Mister.php?action=get_product_auto_cota',
                                type: 'POST',
                                data: { product_id: product_id },
                                success: function(response) {
                                    var res = JSON.parse(response);
                                    
                                    if (res.status === 'success') {
                                        $('#status_auto_cota').prop('checked', res.status_auto_cota == 1);
                                        $('#tipo_auto_cota').val(res.tipo_auto_cota);
                                        $('#quantidade_auto_cota').prop('checked', res.quantidade_auto_cota == 1);
                                        if (res.status_auto_cota == 1) {
                                            $('.status_cotas').show();
                                        } else {
                                            $('.status_cotas').hide();
                                        }
                                    }
                                },
                                error: function() {
                                    console.error('Erro ao buscar configurações!');
                                }
                            });
                        }

                    


 document.getElementById('status_auto_cota').addEventListener('change', function() {
        if ($(this).is(':checked')) {
            $(this).val(1);
            $('.status_cotas').show();
        } else {
            $(this).val(0);
            $('.status_cotas').hide();
            
        }
    }
    );

  document.getElementById('cotas_premiadas').addEventListener('change', function(e) {
    var new_value = $(this).val();
    $('#tipo_auto_cota').val(new_value);
    console.log(new_value);
    
    
                 

});
    


    $('#product-form').submit(function(e) {

        $(document).ajaxSuccess(async function(event, xhr, settings) {
            // Check if the request URL matches the one you want to intercept
            if (settings.url === _base_url_ + 'class/Main.php?action=save_product_sys') {
                var response = xhr.responseJSON; // Parse the JSON response
                if (response) {
                    if (response.status == 'success'){
                        
                        var product_id = response.pid;
                        var status_auto_cota = $('#status_auto_cota').is(':checked') ? 1 : 0;
                        var quantidade_auto_cota = $('#quantidade_auto_cota').is(':checked') ? 1 : 0;

                        var tipo_auto_cota = $('#tipo_auto_cota').val();
                      var formData = new FormData();
                        formData.append('product_id', product_id);
                        formData.append('status_auto_cota', status_auto_cota);
                        formData.append('tipo_auto_cota', tipo_auto_cota);
                        formData.append('quantidade_auto_cota', quantidade_auto_cota);



                        var response = await $.ajax({
                            url: _base_url_ + 'class/Mister.php?action=save_product_auto_cota',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            method: 'POST',

                            success: function(response) {
                                var res = JSON.parse(response);
                                console.log(res);
                            },
                            error: function() {
                                console.error('Erro ao salvar configurações!');
                            }


                        });
                    }
                }
            }
        }
        );

        


        
    });

}
}
if (page.includes('manage_product')) {
    
    // Seleciona todas as divs especificadas que contêm as tags <a>
    var actions = $('body > div > div.flex.flex-col.flex-1 > main > div > div > div.w-full.overflow-x-auto > table > tbody > tr > td:nth-child(7) > div');

     
    // Itera sobre cada linha da tabela
    $('body > div > div.flex.flex-col.flex-1 > main > div > div > div.w-full.overflow-x-auto > table > tbody > tr').each(function() {
        // Seleciona a div de ações e o ID correspondente dentro da mesma linha
        var actionDiv = $(this).find('td:nth-child(7) > div');
        var id = $(this).find('td:nth-child(7) > div > a:nth-child(2)').attr('href').split('/')[2].split('=')[1];

        console.log(id);

        // Cria a nova tag <a> com o nome correspondente como texto
        var newLink = $('<a>', {
            href: '#' + id, // Define o href do novo link
            text: 'Duplicar', // Define o texto do novo link
            id: 'duplicate-' + id, // Define um ID único para o link
            click: function(e) { // Adiciona o evento de clique
                e.preventDefault();
                duplicateRaffle(id); // Chama a função de duplicação com o ID
            }
        });

        // Adiciona a nova tag <a> dentro da div de ações
        actionDiv.append(newLink);
    });
    
}
    
    
});

function duplicateRaffle(id) {
    var now = new Date();
    var date = now.getFullYear() + '-' + (now.getMonth() + 1) + '-' + now.getDate();
    var time = now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();
    var dateTime = date + ' ' + time;

    $.ajax({
        url: '/class/Mister.php?action=duplicate_product',
        type: 'POST',
        data: { id: id, dateTime: dateTime },
        success: function(response) {
            var res = JSON.parse(response);
            
            if (res.status === 'success') {
                alert('Rifa duplicada com sucesso!');
                var product = $('body > div > div.flex.flex-col.flex-1 > main > div > div > div.w-full.overflow-x-auto > table > tbody > tr').filter(function() {
                    return $(this).find('td:nth-child(7) > div > a:nth-child(2)').attr('href').split('/')[2].split('=')[1] === res.pid.toString();
                });

                product.css('background-color', 'green').css('color', 'white').css('animation', 'blink 1s linear infinite');
                setTimeout(function() {
                    product.css('background-color', '').css('color', '').css('animation', '');
                }, 5000);
                location.reload();
            } else {
                alert('Erro ao duplicar rifa!');
            }
        },
        error: function() {
            alert('Erro ao duplicar rifa!');
        }
    });
}


