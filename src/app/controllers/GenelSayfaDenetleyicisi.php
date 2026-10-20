<?php
// ============================================================
// StaticController - statik içerik (SSS, kontrol listesi)
// Giriş yapmadan erişilebilir (bilgi verme sayfaları).
// ============================================================
declare(strict_types=1);

namespace App\Controllers;

use App\Controller;
use App\Models\FaqItem;
use App\Models\ChecklistItem;

class StaticController extends Controller
{
    public function middleware(): array
    {
        return []; // herkese açık
    }

    public function faq(): void
    {
        $items = (new FaqItem())->active();
        $this->view('static/sss', ['items' => $items], 'app');
    }

        public function checklist(): void
    {
        $items = (new ChecklistItem())->active();
        $this->view('static/kontrol_listesi', ['items' => $items], 'app');
    }

    /** KVKK aydınlatma metni (statik). */
    public function kvkk(): void
    {
        $this->view('static/kvkk', [], 'app');
    }
}
