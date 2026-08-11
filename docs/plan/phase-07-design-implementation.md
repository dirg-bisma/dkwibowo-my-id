# Phase 7 — Design Implementation dkwibowo.my.id

**Status:** In progress — visual implementation dan Docker verification selesai; content fixture, final accessibility, dan SEO review masih tersisa  
**Reference:** `docs/design.md`, `docs/example/home.html`  
**Asset utama:** `assets/img/profile.webp`

## Tujuan

Menerapkan konsep visual dark technical editorial ke aplikasi yang sudah berjalan tanpa mengubah invariant content, lifecycle, security, dan SEO dari FSD/TSD.

## Prinsip Implementasi

- mempertahankan PHP native dan view server-rendered yang sudah ada;
- menggunakan asset lokal untuk production;
- menjadikan `dkwibowo.my.id` sebagai satu-satunya brand;
- mempertahankan URL bilingual `/id` dan `/en`;
- menjadikan motion/parallax sebagai enhancement, bukan dependency untuk membaca konten;
- tidak menampilkan content draft, archived, atau trashed;
- tidak memasukkan section/menu yang belum disepakati ke implementasi final.

## Phase 7.0 — Finalisasi Information Architecture

- [x] Kunci menu public: Work, Skills, Experience, Project, About, Contact.
- [x] Kunci section homepage: profile introduction, Skills, Experience, dan maksimal 5 project terbaru.
- [x] Work menjadi anchor ke section project homepage.
- [x] Project menjadi archive seluruh project published.
- [x] Tetapkan Skills, Experience, dan About sebagai section statis.
- [x] Tentukan target utama homepage: personal profile dan project discovery.
- [x] Tentukan CTA utama menuju email dan WhatsApp.
- [x] Tentukan link sosial: LinkedIn dan GitHub.

**Output:** sitemap public dan content contract homepage.

## Phase 7.1 — Visual Foundation

- [x] Tambahkan stylesheet lokal untuk design tokens.
- [x] Tetapkan warna background, surface, border, text, dan accent blue dari `docs/design.md`.
- [x] Pilih satu font primary dan JetBrains Mono sebagai technical accent.
- [x] Buat utility untuk container, section spacing, focus state, dan responsive breakpoint.
- [x] Hapus ketergantungan Tailwind CDN dari halaman production.
- [x] Tambahkan asset lokal dan metadata image yang sesuai.

**Output:** foundation CSS yang dapat digunakan oleh seluruh view public.

## Phase 7.2 — Application Shell

- [x] Restyle global layout `views/layouts/main.php`.
- [x] Buat wordmark `dkwibowo.my.id` pada navigation dan footer.
- [x] Implementasikan active navigation state.
- [x] Implementasikan language switcher ID/EN yang mempertahankan konteks URL dan query parameter.
- [x] Implementasikan mobile menu dengan keyboard support, focus management, dan accessible label.
- [x] Tambahkan footer dengan contact dan link yang telah disetujui.

**Output:** shell public bilingual yang konsisten.

## Phase 7.3 — Homepage Hero

- [x] Buat hero bilingual berdasarkan content yang disepakati.
- [x] Gunakan `assets/img/profile.webp` sebagai profile image lokal.
- [x] Sesuaikan crop, aspect ratio, dan object position melalui frontend tanpa mengubah source image.
- [x] Gunakan copy hero dalam English.
- [ ] Pertahankan aspect ratio dan crop yang sesuai untuk desktop/mobile.
- [ ] Tambahkan alt text yang bermakna dalam ID dan EN.
- [x] Tambahkan status badge atau technical label hanya jika memiliki makna nyata.
- [x] Tambahkan CTA utama dan social proof/link yang valid.
- [x] Gunakan visual treatment: surface panel, border halus, aksen biru, dan parallax ringan.

**Output:** hero responsive dengan profile image sebagai visual anchor.

## Phase 7.4 — Public Content Components

- [x] Restyle homepage project listing sesuai design token.
- [x] Implementasikan project card dengan title, project type, cover, dan link detail.
- [x] Sediakan fallback visual jika `cover_image` kosong.
- [ ] Implementasikan bento grid hanya jika jumlah dan prioritas project mendukung.
- [x] Ambil maksimal lima project terbaru untuk section homepage.
- [x] Gunakan cover project dari upload admin sebagai visual utama.
- [x] Restyle detail content, tag page, search page, pagination, dan empty state.
- [ ] Pastikan informasi penting tidak hanya muncul pada hover.
- [ ] Pertahankan status filter: hanya content `published` yang tampil.

**Output:** komponen portfolio yang terhubung ke metadata dan Markdown existing.

## Phase 7.5 — Motion dan Parallax

- [x] Buat satu modul JavaScript lokal untuk scroll reveal.
- [x] Tambahkan staggered reveal pada section/card.
- [x] Tambahkan parallax ringan pada background decoration dan hero image.
- [x] Tambahkan navbar transition saat scroll.
- [x] Tambahkan hover transform/opacity untuk project card.
- [x] Gunakan `IntersectionObserver` dan `requestAnimationFrame`.
- [x] Hormati `prefers-reduced-motion`.
- [x] Pastikan konten visible dan usable tanpa JavaScript.

**Output:** motion system konsisten dan progressive enhancement.

## Phase 7.6 — SEO, Accessibility, dan Performance

- [ ] Pastikan canonical, hreflang, OG, schema, dan description tetap valid setelah restyle.
- [ ] Pastikan heading hierarchy sesuai struktur section.
- [ ] Tambahkan visible focus state untuk link, button, dan mobile menu.
- [ ] Audit kontras warna terhadap background dark.
- [ ] Tambahkan `loading="lazy"` pada image non-hero.
- [ ] Tambahkan dimensi image untuk mengurangi layout shift.
- [ ] Pastikan profile image dan project image memiliki alt text.
- [ ] Uji halaman tanpa external font/image placeholder.

**Output:** design yang usable, indexable, dan tidak mengorbankan performa.

## Phase 7.7 — Verification

- [x] Uji `/id` dan `/en` pada desktop dan mobile viewport.
- [ ] Uji language switching dari homepage, search, tag, dan detail.
- [ ] Uji content published, draft, archived, dan trashed.
- [ ] Uji empty state dan pagination.
- [ ] Uji mobile menu dengan keyboard.
- [ ] Uji reduced motion.
- [ ] Uji private storage dan database tetap tidak terekspos.
- [x] Jalankan PHP lint dan Docker smoke test.
- [x] Perbarui `docs/design.md` menjadi baseline design yang disepakati.

## Definition of Done

- [x] Sitemap/menu dan section homepage utama sudah disepakati.
- [x] Design token dan typography diterapkan konsisten.
- [x] `profile.webp` digunakan sebagai asset hero lokal.
- [x] Homepage dan halaman public existing memiliki visual system yang sama.
- [x] ID/EN, status visibility, search, tag, pagination, dan detail tetap memiliki route existing.
- [x] Mobile layout dan mobile menu usable.
- [x] Motion/parallax berfungsi dan memiliki reduced-motion fallback.
- [x] Tidak ada remote placeholder asset atau Tailwind CDN pada production.
- [ ] Accessibility, SEO, dan performance check lulus.

## Keputusan yang Masih Dibutuhkan

Keputusan informasi arsitektur dan copywriting sudah selesai. Sisa pekerjaan adalah verification dengan fixture content dan audit final accessibility/SEO.
