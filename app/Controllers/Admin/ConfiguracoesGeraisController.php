<?php
namespace App\Controllers\Admin;

use App\Helpers\Uploader;
use App\Models\Configuracao;
use Core\Controller;

class ConfiguracoesGeraisController extends Controller
{
    public function index(): void
    {
        $this->render('admin/configuracoes/gerais.twig', [
            'titulo' => 'Configurações Gerais',
            'config' => (new Configuracao())->getAll()
        ]);
    }

    public function update(): void
    {
        $configModel = new Configuracao();
        $currentConfig = $configModel->getAll();

        // Lida com a remoção do logo do admin
        if (isset($_POST['remover_logo_admin']) && $_POST['remover_logo_admin'] == '1') {
            Uploader::delete($currentConfig['logo_admin'] ?? null);
            $configModel->update('logo_admin', null);
        }
        // Lida com o upload do logo do admin
        elseif (isset($_FILES['logo_admin']) && $_FILES['logo_admin']['error'] == UPLOAD_ERR_OK) {
            Uploader::delete($currentConfig['logo_admin'] ?? null);
            $_POST['logo_admin'] = Uploader::upload($_FILES['logo_admin'], 'logos');
        }

        // Lida com a remoção do logo do frontend
        if (isset($_POST['remover_logo_frontend']) && $_POST['remover_logo_frontend'] == '1') {
            Uploader::delete($currentConfig['logo_frontend'] ?? null);
            $configModel->update('logo_frontend', null);
        }
        // Lida com o upload do logo do frontend
        elseif (isset($_FILES['logo_frontend']) && $_FILES['logo_frontend']['error'] == UPLOAD_ERR_OK) {
            Uploader::delete($currentConfig['logo_frontend'] ?? null);
            $_POST['logo_frontend'] = Uploader::upload($_FILES['logo_frontend'], 'logos');
        }
        
        // Salva todas as chaves que vieram do POST
        foreach ($_POST as $chave => $valor) {
            if (!empty($chave) && !str_starts_with($chave, 'remover_')) {
                $configModel->update($chave, $valor);
            }
        }

        header('Location: /admin/configuracoes/gerais');
        exit();
    }
}
