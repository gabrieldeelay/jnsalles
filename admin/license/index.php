<?php
class LicenseSystem
{
    public $key = 'D9E6FD32C2C4C9E5';
    private $product_id = '9';
    private $product_base = 'raffle-system';
    private $server_host = '';
    private static $selfobj = null;

    public function __construct()
    {
        // Desabilitando inicialização do handler de ação
        // $this->initActionHandler();
    }

    public function initActionHandler()
    {
        // Desabilitando handler de ação
        // $handler = hash('crc32b', $this->product_id . $this->key . $this->getDomain()) . '_handle';
        // if (isset($_GET['action']) && $handler == $_GET['action']) {
        //     $this->handleServerRequest();
        //     exit();
        // }
    }

    public function handleServerRequest()
    {
        // Desabilitando requisição ao servidor
        // $type = (isset($_GET['type']) ? strtolower($_GET['type']) : '');

        // switch ($type) {
        //     case 'rl':
        //         $this->removeOldResponse();
        //         $obj = new stdClass();
        //         $obj->product = $this->product_id;
        //         $obj->status = true;
        //         echo $this->encryptObj($obj);
        //         return NULL;
        //     case 'dl':
        //         $obj = new stdClass();
        //         $obj->product = $this->product_id;
        //         $obj->status = true;
        //         $this->removeOldResponse();
        //         echo $this->encryptObj($obj);
        //         return NULL;
        // }

        return NULL;
    }

    public function __plugin_updateInfo()
    {
        $responseJson = new stdClass();
        $responseJson->data = new stdClass();
        $responseJson->data->new_version = '1.0.0';
        $responseJson->data->version = '1.0.0';
        $responseJson->data->url = '';
        $responseJson->data->package = '';
        $responseJson->data->sections = [];
        $responseJson->data->icons = [];
        $responseJson->data->banners = [];
        $responseJson->data->banners_rtl = [];
        return $responseJson->data;
    }

    public static function GetPluginUpdateInfo()
    {
        $obj = static::getInstance();
        return $obj->__plugin_updateInfo();
    }

    public static function &getInstance()
    {
        if (empty(static::$selfobj)) {
            static::$selfobj = new static();
        }

        return static::$selfobj;
        return NULL;
    }

    private function encrypt($plainText, $password = '')
    {
        if (empty($password)) {
            $password = $this->key;
        }

        $plainText = rand(10, 99) . $plainText . rand(10, 99);
        $method = 'aes-256-cbc';
        $key = substr(hash('sha256', $password, true), 0, 32);
        $iv = substr(strtoupper(md5($password)), 0, 16);
        return base64_encode(openssl_encrypt($plainText, $method, $key, OPENSSL_RAW_DATA, $iv));
    }

    private function decrypt($encrypted, $password = '')
    {
        if (empty($password)) {
            $password = $this->key;
        }

        $method = 'aes-256-cbc';
        $key = substr(hash('sha256', $password, true), 0, 32);
        $iv = substr(strtoupper(md5($password)), 0, 16);
        $plaintext = openssl_decrypt(base64_decode($encrypted), $method, $key, OPENSSL_RAW_DATA, $iv);
        return substr($plaintext, 2, -2);
    }

    public function encryptObj($obj)
    {
        $text = serialize($obj);
        return $this->encrypt($text);
    }

    private function decryptObj($ciphertext)
    {
        $text = $this->decrypt($ciphertext);
        return unserialize($text);
    }

    private function getDomain()
    {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http');
        $base_url .= '://' . $_SERVER['HTTP_HOST'];
        $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        return $base_url;
    }

    private function getEmail()
    {
        return '';
    }

    private function processs_response($response)
    {
        $resbk = '';

        if (!empty($response)) {
            if (!empty($this->key)) {
                $resbk = $response;
                $response = $this->decrypt($response);
            }

            $response = json_decode($response);

            if (is_object($response)) {
                return $response;
            } else {
                $response = new stdClass();
                $response->status = false;
                $bkjson = @json_decode($resbk);

                if (!empty($bkjson->msg)) {
                    $response->msg = $bkjson->msg;
                } else {
                    $response->msg = 'Response Error, contact with the author or update the plugin or theme';
                }

                $response->data = NULL;
                return $response;
            }
        }

        $response = new stdClass();
        $response->msg = 'unknown response';
        $response->status = false;
        $response->data = NULL;
        return $response;
    }

    private function _request($relative_url, $data, &$error = '')
    {
        $response = new stdClass();
        $response->status = true;
        $response->msg = 'Success';
        return $response;
    }

    private function getParam($purchase_key, $app_version, $admin_email = '')
    {
        $req = new stdClass();
        $req->license_key = $purchase_key;
        $req->email = (!empty($admin_email) ? $admin_email : $this->getEmail());
        $req->domain = $this->getDomain();
        $req->app_version = $app_version;
        $req->product_id = $this->product_id;
        $req->product_base = $this->product_base;
        return $req;
    }

    public function SaveResponse($response)
    {
        // Desabilitando salvamento de resposta
        // $key = hash('crc32b', $this->getDomain() . $this->product_id . 'LIC');
        // $data = $this->encrypt(serialize($response), $this->getDomain());
        // file_put_contents(dirname(__FILE__) . '/' . $key, $data);
    }

    public function getOldResponse()
    {
        // Desabilitando obtenção de resposta antiga
        // $key = hash('crc32b', $this->getDomain() . $this->product_id . 'LIC');

        // if (file_exists(dirname(__FILE__) . '/' . $key)) {
        //     $response = file_get_contents(dirname(__FILE__) . '/' . $key);

        //     if (!empty($response)) {
        //         return unserialize($this->decrypt($response, $this->getDomain()));
        //     }
        // }

        return NULL;
    }

    private function removeOldResponse()
    {
        // Desabilitando remoção de resposta antiga
        // $key = hash('crc32b', $this->getDomain() . $this->product_id . 'LIC');

        // if (file_exists(dirname(__FILE__) . '/' . $key)) {
        //     unlink(dirname(__FILE__) . '/' . $key);
        // }

        return true;
    }

    public static function RemoveLicenseKey(&$message = '', $version = '')
    {
        $message = 'License removed successfully';
        return true;
    }

    public static function CheckLicense($purchase_key, &$error = '', &$responseObj = NULL, $app_version = '', $admin_email = '')
    {
        $responseObj = new stdClass();
        $responseObj->is_valid = true;
        $responseObj->next_request = time() + 3600;
        $responseObj->expire_date = '2099-12-31';
        $responseObj->support_end = '2099-12-31';
        $responseObj->license_title = 'Lifetime License';
        $responseObj->license_key = $purchase_key;
        return true;
    }

    public static function GetRegisterInfo()
    {
        if (!empty(static::$selfobj)) {
            return static::$selfobj->getOldResponse();
        }

        return NULL;
    }
}

if ($_settings->userdata('type') != '1') {
    echo 'Você não tem permissão para acessar essa página.';
    exit();
}

$errorMessage = '';
$responseObj = NULL;
$version = APP_VERSION;
$licenseKey = '';
$adminEmail = '';
$license_key = $_settings->info('license');
require_once 'license.php';
echo '<style>' . "\r\n" . '.active-tab{border-bottom:none!important}.can-toggle{position:relative;margin-bottom:20px}.can-toggle *,.can-toggle :after,.can-toggle :before{box-sizing:border-box}.can-toggle input[type=checkbox]{opacity:0;position:absolute;top:0;left:0}.can-toggle input[type=checkbox]:focus+.can-toggle__switch{box-shadow:0 0 3px rgba(42,152,239,.8)}.can-toggle__switch{background-color:#dddddd;border-radius:60px;cursor:pointer;display:inline-block;height:20px;outline:0;padding:1px;position:relative;transition:background .15s ease;width:36px}.can-toggle__switch:before{background-color:#fff;border-radius:50%;bottom:0;content:"";height:18px;left:0;position:absolute;top:0;transition:left .15s ease;width:18px}.can-toggle input[type=checkbox]:checked+.can-toggle__switch{background-color:#26ca28}.can-toggle input[type=checkbox]:checked+.can-toggle__switch:before{left:16px}.can-toggle label{color:#000;cursor:pointer;display:block;font-size:14px;margin-left:10px;padding:0 10px 0 0}.can-toggle label,.can-toggle span{line-height:22px}';
echo '</style>';
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Gerenciamento de Licenças</h3>
    </div>
    <div class="card-body">
        <div class="container-fluid">
            <div id="msg"></div>
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 col-sm-12 col-xs-12">
                    <form id="validate-license-form" action="" method="post">
                        <div class="form-group">
                            <label for="license_key">Chave de Licença</label>
                            <input type="text" class="form-control form-control-lg" id="license_key" name="license_key" value="<?php echo $_settings->info('license') ?>" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="admin_email">Email do Administrador</label>
                            <input type="email" class="form-control form-control-lg" id="admin_email" name="admin_email" value="<?php echo $_settings->userdata('email') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="app_version">Versão do Sistema</label>
                            <input type="text" class="form-control form-control-lg" id="app_version" name="app_version" value="<?php echo APP_VERSION ?>" readonly>
                        </div>
                        <div class="form-group text-center">
                            <button class="btn btn-primary btn-lg">Salvar Licença</button>
                        </div>
                    </form>
                    <hr>
                    <form id="remove-license-form" action="" method="post">
                        <div class="form-group text-center">
                            <button class="btn btn-danger btn-lg">Remover Licença</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $('#validate-license-form').submit(function(e){
            e.preventDefault();
            var _this = $(this);
            $('.pop_msg').remove()
            var el = $('<div>')
            el.addClass('pop_msg')
            _this.find('button[type="submit"]').attr('disabled',true)
            _this.find('button[type="submit"]').text('Validando Licença...')
            $.ajax({
                url:_base_url_+'classes/Master.php?f=validate_license',
                method:'POST',
                data:_this.serialize(),
                dataType:'json',
                error:err=>{
                    console.log(err)
                    el.addClass('alert alert-danger')
                    el.text("Ocorreu um erro ao salvar a licença.")
                    _this.prepend(el)
                    _this.find('button[type="submit"]').attr('disabled',false)
                    _this.find('button[type="submit"]').text('Salvar Licença')
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        el.addClass('alert alert-success')
                    }else{
                        el.addClass('alert alert-danger')
                    }
                    el.text(resp.msg)
                    _this.prepend(el)
                    _this.find('button[type="submit"]').attr('disabled',false)
                    _this.find('button[type="submit"]').text('Salvar Licença')
                }
            })
        })
        $('#remove-license-form').submit(function(e){
            e.preventDefault();
            var _this = $(this);
            $('.pop_msg').remove()
            var el = $('<div>')
            el.addClass('pop_msg')
            _this.find('button[type="submit"]').attr('disabled',true)
            _this.find('button[type="submit"]').text('Removendo Licença...')
            $.ajax({
                url:_base_url_+'classes/Master.php?f=remove_license',
                method:'POST',
                dataType:'json',
                error:err=>{
                    console.log(err)
                    el.addClass('alert alert-danger')
                    el.text("Ocorreu um erro ao remover a licença.")
                    _this.prepend(el)
                    _this.find('button[type="submit"]').attr('disabled',false)
                    _this.find('button[type="submit"]').text('Remover Licença')
                },
                success:function(resp){
                    if(resp.status == 'success'){
                        el.addClass('alert alert-success')
                        $('#validate-license-form')[0].reset()
                    }else{
                        el.addClass('alert alert-danger')
                    }
                    el.text(resp.msg)
                    _this.prepend(el)
                    _this.find('button[type="submit"]').attr('disabled',false)
                    _this.find('button[type="submit"]').text('Remover Licença')
                }
            })
        })
    })
</script>
