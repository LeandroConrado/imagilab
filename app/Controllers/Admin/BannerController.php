<?php

namespace App\Controllers\Admin;

use App\Helpers\Uploader;
use App\Models\Banner;
use Core\Controller;

class BannerController extends Controller
{
    public function index(): void
    {
        $this->render('admin/banners/index.twig', [
            'titulo' => 'Gerenciamento de Banners',
            'banners' => (new Banner())->findAll()
        ]);
    }

    public function create(): void
    {
        $this->render('admin/banners/create.twig', ['titulo' => 'Novo Banner']);
    }

    private function getRequestData(array $currentBanner = []): array
    {
        // Lógica de Upload de Imagens
        $imagemDesktop = $currentBanner['imagem_desktop'] ?? null;
        if (isset($_FILES['imagem_desktop']) && $_FILES['imagem_desktop']['error'] == UPLOAD_ERR_OK) {
            Uploader::delete($imagemDesktop);
            $imagemDesktop = Uploader::upload($_FILES['imagem_desktop'], 'banners');
        }

        $imagemMobile = $currentBanner['imagem_mobile'] ?? null;
        if (isset($_FILES['imagem_mobile']) && $_FILES['imagem_mobile']['error'] == UPLOAD_ERR_OK) {
            Uploader::delete($imagemMobile);
            $imagemMobile = Uploader::upload($_FILES['imagem_mobile'], 'banners');
        }
        
        return [
            'titulo' => $_POST['titulo'] ?? null,
            'descricao' => $_POST['descricao'] ?? null,
            'imagem_desktop' => $imagemDesktop,
            'imagem_mobile' => $imagemMobile,
            'cor_fundo_1' => $_POST['cor_fundo_1'] ?? '#FFFFFF',
            'cor_fundo_2' => $_POST['cor_fundo_2'] ?? null,
            'paginas_exibicao' => isset($_POST['paginas_exibicao']) ? json_encode($_POST['paginas_exibicao']) : null,
            'link_botao' => $_POST['link_botao'] ?? null,
            'texto_botao' => $_POST['texto_botao'] ?? null,
            'data_inicio' => $_POST['data_inicio'] ?: null,
            'data_fim' => $_POST['data_fim'] ?: null,
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
        ];
    }

    public function store(): void
    {
        $data = $this->getRequestData();
        if ($data['titulo']) {
            (new Banner())->create($data);
        }
        header('Location: /admin/banners');
        exit();
    }

    public function edit(int $id): void
    {
        $banner = (new Banner())->findById($id);
        if (!$banner) {
            header('Location: /admin/banners');
            exit();
        }
        $banner['paginas_exibicao'] = json_decode($banner['paginas_exibicao'] ?? '[]', true);
        $this->render('admin/banners/edit.twig', ['titulo' => 'Editar Banner', 'banner' => $banner]);
    }

    public function update(int $id): void
    {
        $bannerModel = new Banner();
        $currentBanner = $bannerModel->findById($id);
        $data = $this->getRequestData($currentBanner);
        if ($data['titulo']) {
            $bannerModel->update($id, $data);
        }
        header('Location: /admin/banners');
        exit();
    }

    public function destroy(int $id): void
    {
        (new Banner())->delete($id);
        header('Location: /admin/banners');
        exit();
    }
}
