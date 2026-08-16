<?php
require_once('../settings.php');


class Customer extends DBConnection
{
    private $settings = null;

    public function __construct()
    {
        global $_settings;
        $this->settings = $_settings;
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function save_users_system()
    {
        if (empty($_POST['password'])) {
            unset($_POST['password']);
        } else {
            $_POST['password'] = md5($_POST['password']);
        }

        extract($_POST);
        $data = '';
        if (empty($id)) {
            if (empty($_POST['type']) || empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['username'])) {
                return 3;
            }
        } else {
            if (empty($_POST['type']) || empty($_POST['firstname']) || empty($_POST['lastname']) || empty($_POST['email']) || empty($_POST['username'])) {
                return 3;
            }
        }


        foreach ($_POST as $k => $v) {
            $v = $this->conn->real_escape_string($v);

            if (!empty($data)) {
                $data .= ', ';
            }

            $data .= ' `' . $k . '` = \'' . $v . '\' ';
        }


        if (empty($id)) {
            $data = str_replace('`id` = \'\' ,', '', $data);

            $qry = $this->conn->query('INSERT INTO users set ' . $data);

            if ($qry) {
                $id = $this->conn->insert_id;

                foreach ($_POST as $k => $v) {
                    if ($k != 'id') {
                        if (!empty($data)) {
                            $data .= ' , ';
                        }

                        if ($id == $this->settings->userdata('id')) {
                            $this->settings->set_userdata($k, $v);
                        }
                    }
                }

                $user_name = $this->settings->userdata('firstname');
                $insert = $this->conn->query('INSERT INTO `logs` (`origin`, `description`) VALUES (\'USER\', \'Usuário ' . $_POST['firstname'] . ' adicionado pelo usuário ' . $user_name . '\')');

                if (!empty($_FILES['img']['tmp_name'])) {
                    if (!is_dir(BASE_APP . 'uploads/avatars')) {
                        mkdir(BASE_APP . 'uploads/avatars');
                    }

                    $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
                    $fname = 'uploads/avatars/' . $id . '.png';
                    $accept = ['image/jpeg', 'image/png'];

                    if (!in_array($_FILES['img']['type'], $accept)) {
                        $err = 'Image file type is invalid';
                    }

                    if ($_FILES['img']['type'] == 'image/jpeg') {
                        $uploadfile = imagecreatefromjpeg($_FILES['img']['tmp_name']);
                    } else if ($_FILES['img']['type'] == 'image/png') {
                        $uploadfile = imagecreatefrompng($_FILES['img']['tmp_name']);
                    }

                    if (!$uploadfile) {
                        $err = 'Image is invalid';
                    }

                    $temp = imagescale($uploadfile, 200, 200);

                    if (is_file(BASE_APP . $fname)) {
                        unlink(BASE_APP . $fname);
                    }

                    $upload = imagepng($temp, BASE_APP . $fname);

                    if ($upload) {
                        $this->conn->query('UPDATE `users` set `avatar` = CONCAT(\'' . $fname . '\', \'?v=\',unix_timestamp(CURRENT_TIMESTAMP)) where id = \'' . $id . '\'');

                        if ($id == $this->settings->userdata('id')) {
                            $this->settings->set_userdata('avatar', $fname . '?v=' . time());
                        }
                    }

                    imagedestroy($temp);
                }

                return 1;
            } else {
                return 2;
            }
        } else {
            $qry = $this->conn->query('UPDATE users set ' . $data . ' where id = ' . $id);

            if ($qry) {
                foreach ($_POST as $k => $v) {
                    if ($k != 'id') {
                        if (!empty($data)) {
                            $data .= ' , ';
                        }

                        if ($id == $this->settings->userdata('id')) {
                            $this->settings->set_userdata($k, $v);
                        }
                    }
                }

                $user_name = $this->settings->userdata('firstname');
                $insert = $this->conn->query('INSERT INTO `logs` (`origin`, `description`) VALUES (\'USER\', \'Usuário ' . $_POST['firstname'] . ' atualizado pelo usuário ' . $user_name . '\')');

                if (!empty($_FILES['img']['tmp_name'])) {
                    if (!is_dir(BASE_APP . 'uploads/avatars')) {
                        mkdir(BASE_APP . 'uploads/avatars');
                    }

                    $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
                    $fname = 'uploads/avatars/' . $id . '.png';
                    $accept = ['image/jpeg', 'image/png'];

                    if (!in_array($_FILES['img']['type'], $accept)) {
                        $err = 'Image file type is invalid';
                    }

                    if ($_FILES['img']['type'] == 'image/jpeg') {
                        $uploadfile = imagecreatefromjpeg($_FILES['img']['tmp_name']);
                    } else if ($_FILES['img']['type'] == 'image/png') {
                        $uploadfile = imagecreatefrompng($_FILES['img']['tmp_name']);
                    }

                    if (!$uploadfile) {
                        $err = 'Image is invalid';
                    }

                    $temp = imagescale($uploadfile, 200, 200);

                    if (is_file(BASE_APP . $fname)) {
                        unlink(BASE_APP . $fname);
                    }

                    $upload = imagepng($temp, BASE_APP . $fname);

                    if ($upload) {
                        $this->conn->query('UPDATE `users` set `avatar` = CONCAT(\'' . $fname . '\', \'?v=\',unix_timestamp(CURRENT_TIMESTAMP)) where id = \'' . $id . '\'');

                        if ($id == $this->settings->userdata('id')) {
                            $this->settings->set_userdata('avatar', $fname . '?v=' . time());
                        }
                    }

                    imagedestroy($temp);
                }

                return 4;
            } else {
                return 'UPDATE users set ' . $data . ' where id = ' . $id;
            }
        }
    }

    public function delete_users_system()
    {
        extract($_POST);

        if (!$this->settings->userdata('firstname')) {
            return 2;
        }

        $usr = $this->conn->query('SELECT * FROM users WHERE id = ' . $id);

        if (0 < $usr->num_rows) {
            $row = $usr->fetch_assoc();
            $u_username = $row['username'];
            $u_firstname = $row['firstname'];
            $u_lastname = $row['lastname'];
            $u_email = $row['email'];
            $u_date_added = date('d/m/Y', strtotime($row['date_added']));
        }

        $qry = $this->conn->query('DELETE FROM users where id = ' . $id);

        if ($qry) {
            $user_name = $this->settings->userdata('firstname');
            $insert = $this->conn->query('INSERT INTO `logs` (`origin`, `description`) VALUES (\'USER\', \'Usuário ' . $u_username . ' (' . $u_firstname . ' ' . $u_lastname . ') criado em ' . $u_date_added . ' deletado pelo usuário ' . $user_name . '\')');

            if (is_file(BASE_APP . ('uploads/avatars/' . $id . '.png'))) {
                unlink(BASE_APP . ('uploads/avatars/' . $id . '.png'));
            }

            return 1;
        } else {
            return false;
        }
    }

    public function registration()
    {
        if (!empty($_POST['password'])) {
            $_POST['password'] = md5($_POST['password']);
        } else {
            unset($_POST['password']);
        }

        if (!empty($_POST['phone_confirm'])) {
            unset($_POST['phone_confirm']);
        }

        $_POST['phone'] = preg_replace('/[^0-9]/', '', $_POST['phone']);
        extract($_POST);
        $id = (isset($id) != '' && isset($id) != null && isset($id) > 0 ? $id : null);
        $data = '';

        if (payment_requires_customer_document() && empty($_POST['cpf'])) {
            $resp['status'] = 'cpf_invalid';
            $resp['msg'] = 'Informe um CPF válido para continuar com o pagamento via VenoPag.';
            return json_encode($resp);
        }
        if ($this->settings->info('enable_legal_age') == 1) {
            $year = date('Y');
            $birth = date('Y', strtotime($birth));

            if (($year - $birth) < 18) {
                $resp['status'] = 'birth_invalid';
                $resp['msg'] = 'Você precisa ser maior de 18 anos para se registrar.';
                return json_encode($resp);
            }
        }

        $check = $this->conn->query('SELECT * FROM `customer_list` where phone = \'' . $phone . '\' ' . (0 < $id ? ' and id!=\'' . $id . '\'' : '') . ' ')->num_rows;

        if (0 < $check) {
            $resp['status'] = 'phone_already';
            $resp['msg'] = 'Esse telefone já está em uso.';
            return json_encode($resp);
        }

        if (!empty($_POST['cpf'])) {
            $cpf_validate = validaCPF($cpf);

            if (!$cpf_validate) {
                $resp['status'] = 'cpf_invalid';
                $resp['msg'] = 'Esse CPF não é válido.';
                return json_encode($resp);
            }

            $cpf = $_POST['cpf'];
            $check = $this->conn->query('SELECT * FROM `customer_list` where cpf = \'' . $cpf . '\' ' . (0 < $id ? ' and id != \'' . $id . '\'' : '') . ' ')->num_rows;

            if (0 < $check) {
                $resp['status'] = 'cpf_already';
                $resp['msg'] = 'Esse CPF já está em uso.';
                return json_encode($resp);
            }
        }

        if (!empty($_POST['email'])) {
            $email = $_POST['email'];
            $check = $this->conn->query('SELECT * FROM `customer_list` where email = \'' . $email . '\' ' . (0 < $id ? ' and id != \'' . $id . '\'' : '') . ' ')->num_rows;

            if (0 < $check) {
                $resp['status'] = 'email_already';
                $resp['msg'] = 'Esse email já está em uso';
                return json_encode($resp);
            }
        }

        foreach ($_POST as $k => $v) {
            $v = $this->conn->real_escape_string($v);

            if (!empty($data)) {
                $data .= ', ';
            }

            $data .= ' `' . $k . '` = \'' . $v . '\' ';
        }
        if (empty($id)) {

            $data = str_replace('`id` = \'\' ,', '', $data);
            $sql = 'INSERT INTO `customer_list` set ' . $data . ' ';
        } else {
            $sql = 'UPDATE `customer_list` set ' . $data . ' where id = \'' . $id . '\' ';
        }

        $save = $this->conn->query($sql);

        if ($save) {
            $uid = (!empty($id) ? $id : $this->conn->insert_id);
            $resp['status'] = 'success';
            $resp['redirect'] = BASE_URL;
            $resp['uid'] = $uid;

            if (!empty($id)) {
                $resp['msg'] = 'User Details has been updated successfully';
            } else {
                $resp['msg'] = 'Your Account has been created successfully';
            }
            if (!empty($uid) && $this->settings->userdata('login_type') != 1) {
                $user = $this->conn->query('SELECT * FROM `customer_list` where id = \'' . $uid . '\' ');

                if (0 < $user->num_rows) {
                    $res = $user->fetch_array();

                    foreach ($res as $k => $v) {
                        $this->settings->set_userdata($k, $v);
                    }

                    $this->settings->set_userdata('login_type', '2');
                }
            }
        } else {
            $resp['status'] = 'failed';
            $resp['msg'] = $this->conn->error;
            $resp['sql'] = $sql;
        }
        if (($resp['status'] == 'success') && isset($resp['msg'])) {
            if ($uid) {
                $dados = [];
                $qry = $this->conn->query('SELECT c.id, c.firstname, c.lastname, c.phone FROM `customer_list` c WHERE c.id = \'' . $uid . '\' ');

                if (0 < $qry->num_rows) {
                    $row = $qry->fetch_assoc();
                    $dados['id'] = $row['id'];
                    $dados['first_name'] = $row['firstname'];
                    $dados['last_name'] = $row['lastname'];
                    $dados['phone'] = $row['phone'];
                    send_event_pixel('CompleteRegistration', $dados);
                }
            }
        }

        return json_encode($resp);
    }

    public function change_password()
    {
        if (!$this->settings->userdata('id')) {
            $resp['status'] = 'failed';
            $resp['msg'] = 'Não autorizado.';
            return json_encode($resp);
        }

        global $_settings;
        $id = $_settings->userdata('id');

        if (!empty($_POST['password'])) {
            $password = md5($_POST['password']);
            $sql = 'UPDATE `customer_list` SET `password` = \'' . $password . '\' WHERE `id` = \'' . $id . '\'';
            $save = $this->conn->query($sql);
            $resp['status'] = 'success';
            $resp['msg'] = 'ok';
            return json_encode($resp);
        } else {
            $resp['status'] = 'failed';
            $resp['msg'] = 'Id não existe';
            return json_encode($resp);
        }
    }

    public function update_customer()
    {
        if (!$this->settings->userdata('firstname')) {
            $resp['status'] = 'failed';
            return json_encode($resp);
        }

        if (!empty($_POST['password'])) {
            $_POST['password'] = md5($_POST['password']);
        } else {
            unset($_POST['password']);
        }

        $_POST['phone'] = preg_replace('/[^0-9]/', '', $_POST['phone']);
        extract($_POST);
        $data = '';

        if (payment_requires_customer_document() && empty($_POST['cpf'])) {
            $resp['status'] = 'cpf_invalid';
            $resp['msg'] = 'Informe um CPF válido para continuar com o pagamento via VenoPag.';
            return json_encode($resp);
        }

        if ($_POST['phone']) {
            $checkPhone = $this->conn->query('SELECT * FROM `customer_list` where phone = \'' . $phone . '\' ' . (0 < $id ? ' and id != \'' . $id . '\'' : '') . ' ')->num_rows;

            if (0 < $checkPhone) {
                $resp['status'] = 'phone_already';
                $resp['msg'] = 'Esse telefone já está em uso.';
                return json_encode($resp);
            }
        }

        if (!empty($_POST['email'])) {
            $checkEmail = $this->conn->query('SELECT * FROM `customer_list` where email = \'' . $email . '\' ' . (0 < $id ? ' and id != \'' . $id . '\'' : '') . ' ')->num_rows;

            if (0 < $checkEmail) {
                $resp['status'] = 'email_already';
                $resp['msg'] = 'Esse email já está em uso.';
                return json_encode($resp);
            }
        }

        if (!empty($_POST['cpf'])) {
            $cpf_validate = validaCPF($cpf);

            if (!$cpf_validate) {
                $resp['status'] = 'cpf_invalid';
                $resp['msg'] = 'Esse CPF não é válido.';
                return json_encode($resp);
            }

            $checkCPF = $this->conn->query('SELECT * FROM `customer_list` where cpf = \'' . $cpf . '\' ' . (0 < $id ? ' and id != \'' . $id . '\'' : '') . ' ')->num_rows;

            if (0 < $checkCPF) {
                $resp['status'] = 'cpf_already';
                $resp['msg'] = 'Esse CPF já está em uso.';
                return json_encode($resp);
            }
        }

        foreach ($_POST as $k => $v) {
            $v = $this->conn->real_escape_string($v);

            if (!empty($data)) {
                $data .= ', ';
            }

            $data .= ' `' . $k . '` = \'' . $v . '\' ';
        }

        if (empty($id)) {
            $sql = 'INSERT INTO `customer_list` set ' . $data . ' ';
        } else {
            $sql = 'UPDATE `customer_list` set ' . $data . ' where id = \'' . $id . '\' ';
        }

        $save = $this->conn->query($sql);

        if ($save) {
            $uid = (!empty($id) ? $id : $this->conn->insert_id);
            $resp['status'] = 'success';
            $resp['msg'] = 'Cadastro atualizado!';
            $resp['redirect'] = BASE_URL . 'user/atualizar-cadastro';
            $resp['uid'] = $uid;

            if (!empty($id)) {
                $resp['msg'] = 'User Details has been updated successfully';
            } else {
                $resp['msg'] = 'Your Account has been created successfully';
            }

            if (!empty($_FILES['img']['tmp_name'])) {
                if (!is_dir(BASE_APP . 'uploads/customers')) {
                    mkdir(BASE_APP . 'uploads/customers');
                }

                $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
                $fname = 'uploads/customers/' . $uid . '.png';
                $accept = ['image/jpeg', 'image/png'];

                if (!in_array($_FILES['img']['type'], $accept)) {
                    $resp['msg'] = 'Image file type is invalid';
                }

                if ($_FILES['img']['type'] == 'image/jpeg') {
                    $uploadfile = imagecreatefromjpeg($_FILES['img']['tmp_name']);
                } else if ($_FILES['img']['type'] == 'image/png') {
                    $uploadfile = imagecreatefrompng($_FILES['img']['tmp_name']);
                }

                if (!$uploadfile) {
                    $resp['msg'] = 'Image is invalid';
                }

                $temp = imagescale($uploadfile, 200, 200);

                if (is_file(BASE_APP . $fname)) {
                    unlink(BASE_APP . $fname);
                }

                $upload = imagepng($temp, BASE_APP . $fname);

                if ($upload) {
                    $this->conn->query('UPDATE `customer_list` set `avatar` = CONCAT(\'' . $fname . '\', \'?v=\',unix_timestamp(CURRENT_TIMESTAMP)) where id = \'' . $uid . '\'');
                }

                imagedestroy($temp);
            }
            if (!empty($uid) && $this->settings->userdata('login_type') != 1) {
                $user = $this->conn->query('SELECT * FROM `customer_list` where id = \'' . $uid . '\' ');

                if (0 < $user->num_rows) {
                    $res = $user->fetch_array();

                    foreach ($res as $k => $v) {
                        if (!is_numeric($k) && $k != 'password') {
                            $this->settings->set_userdata($k, $v);
                        }
                    }

                    $this->settings->set_userdata('login_type', '2');
                }
            }
        } else {
            $resp['status'] = 'failed';
            $resp['msg'] = $this->conn->error;
            $resp['sql'] = $sql;
        }
        if (($resp['status'] == 'success') && isset($resp['msg'])) {
            $this->settings->set_flashdata('success', $resp['msg']);
        }

        return json_encode($resp);
    }

    public function delete_customer_system()
    {
        if (!$this->settings->userdata('firstname')) {
            $resp['status'] = 'failed';
            $resp['msg'] = 'Não autorizado.';
            return json_encode($resp);
        }

        extract($_POST);
        $avatarResult = $this->conn->query('SELECT avatar FROM customer_list where id = ' . $id);
        $qry = $this->conn->query('DELETE FROM customer_list where id = ' . $id);

        if ($qry) {
            $resp['status'] = 'success';

            if (0 < $avatarResult->num_rows) {
                $avatarRow = $avatarResult->fetch_array();
                $avatar = $avatarRow[0];

                if ($avatar !== null) {
                    $avatarParts = explode('?', $avatar);
                    $avatarPath = $avatarParts[0];

                    if (is_file(BASE_APP . $avatarPath)) {
                        unlink(BASE_APP . $avatarPath);
                    }
                }
            }
        } else {
            $resp['status'] = 'failed';
            $resp['msg'] = $this->conn->error;
        }

        return json_encode($resp);
    }

    public function import_customers()
    {
        if (empty($this->settings->userdata('firstname')) || (int) $this->settings->userdata('type') !== 1) {
            http_response_code(403);
            return json_encode(['status' => 'failed', 'msg' => 'Não autorizado.']);
        }
        $file = $_FILES['customer_file'] ?? null;
        if (!$file || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return json_encode(['status' => 'failed', 'msg' => 'Selecione um arquivo CSV válido.']);
        }
        if ((int) ($file['size'] ?? 0) <= 0 || (int) $file['size'] > 3 * 1024 * 1024) {
            return json_encode(['status' => 'failed', 'msg' => 'O CSV deve ter no máximo 3 MB.']);
        }

        $contents = file_get_contents($file['tmp_name']);
        if ($contents === false || trim($contents) === '') {
            return json_encode(['status' => 'failed', 'msg' => 'O arquivo está vazio ou não pôde ser lido.']);
        }
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents);
        if (function_exists('mb_check_encoding') && !mb_check_encoding($contents, 'UTF-8')) {
            $contents = mb_convert_encoding($contents, 'UTF-8', 'Windows-1252,ISO-8859-1');
        }
        $lines = preg_split('/\r\n|\n|\r/', $contents);
        $lines = array_values(array_filter($lines, static function ($line) {
            return trim((string) $line) !== '';
        }));
        if (!$lines) {
            return json_encode(['status' => 'failed', 'msg' => 'O CSV não possui linhas para importar.']);
        }
        if (count($lines) > 5001) {
            return json_encode(['status' => 'failed', 'msg' => 'Importe no máximo 5.000 clientes por arquivo.']);
        }

        $firstLine = (string) $lines[0];
        $delimiterCounts = [';' => substr_count($firstLine, ';'), ',' => substr_count($firstLine, ','), "\t" => substr_count($firstLine, "\t")];
        arsort($delimiterCounts);
        $delimiter = (string) array_key_first($delimiterCounts);
        if (($delimiterCounts[$delimiter] ?? 0) === 0) {
            return json_encode(['status' => 'failed', 'msg' => 'Não foi possível identificar as colunas. Use CSV separado por vírgula ou ponto e vírgula.']);
        }

        $normalizeHeader = static function ($value) {
            $value = trim(mb_strtolower((string) $value, 'UTF-8'));
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            $value = $converted !== false ? $converted : $value;
            return preg_replace('/[^a-z0-9]+/', '', $value);
        };
        $headerAliases = [
            'name' => ['nome', 'name', 'cliente', 'customer'],
            'firstname' => ['firstname', 'primeironome'],
            'lastname' => ['lastname', 'sobrenome'],
            'phone' => ['telefone', 'phone', 'celular', 'whatsapp'],
            'email' => ['email', 'emailaddress'],
            'cpf' => ['cpf', 'documento', 'document'],
        ];
        $firstRow = str_getcsv($firstLine, $delimiter);
        $columnMap = [];
        foreach ($firstRow as $index => $heading) {
            $normalized = $normalizeHeader($heading);
            foreach ($headerAliases as $field => $aliases) {
                if (in_array($normalized, $aliases, true)) {
                    $columnMap[$field] = $index;
                    break;
                }
            }
        }
        $hasHeader = isset($columnMap['phone']) && (isset($columnMap['name']) || isset($columnMap['firstname']));
        if (!$hasHeader) {
            $columnMap = count($firstRow) >= 5 && ctype_digit(trim((string) ($firstRow[0] ?? '')))
                ? ['name' => 1, 'phone' => 2, 'email' => 3, 'cpf' => 4]
                : ['name' => 0, 'phone' => 1, 'email' => 2, 'cpf' => 3];
        }

        $duplicatePhone = $this->conn->prepare('SELECT id FROM customer_list WHERE phone = ? LIMIT 1');
        $duplicateEmail = $this->conn->prepare("SELECT id FROM customer_list WHERE email = ? AND email <> '' LIMIT 1");
        $insert = $this->conn->prepare('INSERT INTO customer_list (firstname, lastname, phone, email, cpf, date_created, date_updated) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
        if (!$duplicatePhone || !$duplicateEmail || !$insert) {
            return json_encode(['status' => 'failed', 'msg' => 'Não foi possível preparar a importação no banco de dados.']);
        }

        $created = 0;
        $skipped = 0;
        $errors = [];
        $startAt = $hasHeader ? 1 : 0;
        for ($lineIndex = $startAt; $lineIndex < count($lines); $lineIndex++) {
            $row = str_getcsv((string) $lines[$lineIndex], $delimiter);
            $get = static function ($field) use ($row, $columnMap) {
                return isset($columnMap[$field]) ? trim((string) ($row[$columnMap[$field]] ?? '')) : '';
            };
            $fullName = $get('name');
            $firstname = $get('firstname');
            $lastname = $get('lastname');
            if ($firstname === '' && $fullName !== '') {
                $parts = preg_split('/\s+/', $fullName, 2);
                $firstname = trim((string) ($parts[0] ?? ''));
                $lastname = trim((string) ($parts[1] ?? ''));
            }
            if ($lastname === '') {
                $lastname = 'Cliente';
            }
            $phone = preg_replace('/\D+/', '', $get('phone'));
            $email = mb_strtolower($get('email'), 'UTF-8');
            $cpf = preg_replace('/\D+/', '', $get('cpf'));
            $lineNumber = $lineIndex + 1;

            if ($firstname === '' || strlen($phone) < 8) {
                $skipped++;
                if (count($errors) < 5) $errors[] = "Linha {$lineNumber}: nome ou telefone inválido.";
                continue;
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                if (count($errors) < 5) $errors[] = "Linha {$lineNumber}: e-mail inválido.";
                continue;
            }
            $duplicatePhone->bind_param('s', $phone);
            $duplicatePhone->execute();
            if ($duplicatePhone->get_result()->num_rows) {
                $skipped++;
                continue;
            }
            if ($email !== '') {
                $duplicateEmail->bind_param('s', $email);
                $duplicateEmail->execute();
                if ($duplicateEmail->get_result()->num_rows) {
                    $skipped++;
                    continue;
                }
            }
            $insert->bind_param('sssss', $firstname, $lastname, $phone, $email, $cpf);
            if ($insert->execute()) {
                $created++;
            } else {
                $skipped++;
                if (count($errors) < 5) $errors[] = "Linha {$lineNumber}: " . $insert->error;
            }
        }
        $duplicatePhone->close();
        $duplicateEmail->close();
        $insert->close();

        return json_encode([
            'status' => 'success',
            'msg' => $created . ' cliente(s) importado(s) e ' . $skipped . ' linha(s) ignorada(s).',
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
        ], JSON_UNESCAPED_UNICODE);
    }
}

$users = new Customer();
$action = (!isset($_GET['action']) ? 'none' : strtolower($_GET['action']));

switch ($action) {
    case 'save_system':
        echo $users->save_users_system();
        break;
    case 'delete_system':
        echo $users->delete_users_system();
        break;
    case 'delete_system_customer':
        echo $users->delete_customer_system();
        break;
    case 'update_customer':
        echo $users->update_customer();
        break;
    case 'change_password_system':
        echo $users->change_password();
        break;
    case 'registration':
        echo $users->registration();
        break;
    case 'import_customers':
        echo $users->import_customers();
        break;
    default:
        break;
}
