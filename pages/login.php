<?php


require_once './settings.php';


?>
<div class="container app-main app-form">
    <form id="form-login" method="get" action=".">
        <div class="perfil app-card card mb-2">
            <div class="card-body">
                <div class="mb-2">
                    <label for="phone" class="form-label">Telefone</label>
                    <input onkeyup="formatarTEL(this);" class="form-control text-black mb-2" name="phone" id="phone" maxlength="15" placeholder="(00) 0000-0000" required="" value="">
                </div>

            </div>
        </div>

        <button type="submit" class="btn btn-secondary btn-wide">Entrar</button>
    </form>
</div>
<script>
    $(document).ready(function() {
        $('#form-login').submit(function(e) {
            e.preventDefault()
            $.ajax({
                url: _base_url_ + "class/Auth.php?action=login_customer",
                method: 'POST',
                type: 'POST',
                data: new FormData($(this)[0]),
                dataType: 'json',
                cache: false,
                processData: false,
                contentType: false,
                error: err => {
                    console.log(err)
                    alert('An error occurred')
                },
                success: function(resp) {
                    console.log(resp)
                    if (resp.status == 'success') {
                        window.location.href = "/";
                    } else if (!!resp.msg) {

                        if (!isset($slug)) {
                            var phone = $('#loginModal #phone').val();
                            $('#aviso-login').html('<div style="color:red;font-size:14px;margin-bottom:10px;">Telefone ou senha incorretos!</div>');

                        } else {
                            var phone = $('#loginModal #phone').val();
                            $('#cadastroModal #phone').val(phone);
                            $('#openCadastro').click();

                        }

                    } else {
                        alert('An error occurred')
                        console.log(resp)
                    }
                }
            })
        })
    })

</script>