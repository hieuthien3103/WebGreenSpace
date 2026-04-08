<?php
require_once __DIR__ . '/bootstrap.php';
require_admin_permission('uploads.manage', 'admin_upload_images.php');

render_admin_header('Quản lý ảnh sản phẩm');
?>

<div class="space-y-8">
    <section class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Bảo mật upload</p>
                <h2 class="mt-2 text-2xl font-extrabold text-[#102118]">Quản lý ảnh sản phẩm</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-[#6e8d7b]">
                    Trang này đã được siết lại với kiểm tra CSRF, xác thực admin, validate URL ảnh và xác minh MIME/metadata khi upload file từ máy.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="check_images.php" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                    Kiểm tra ảnh
                </a>
                <a href="fix_images.php" class="inline-flex items-center rounded-full border border-[#d9e9de] px-4 py-2 text-sm font-semibold text-[#102118] transition-colors hover:border-[#2e9b63] hover:text-[#2e9b63]">
                    Chuẩn hóa đường dẫn
                </a>
            </div>
        </div>
    </section>

    <section class="grid gap-8 xl:grid-cols-2">
        <article class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Cách 1</p>
            <h3 class="mt-2 text-2xl font-extrabold text-[#102118]">Cập nhật bằng URL</h3>
            <p class="mt-2 text-sm text-[#6e8d7b]">Chỉ chấp nhận URL hợp lệ dùng `http` hoặc `https`.</p>

            <form method="POST" action="update_product_image.php" class="mt-6 space-y-4">
                <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">

                <div class="space-y-2">
                    <label for="product_id" class="text-sm font-semibold text-[#102118]">Chọn sản phẩm</label>
                    <select id="product_id" name="product_id" required class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                        <option value="">Đang tải danh sách sản phẩm...</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label for="image_url" class="text-sm font-semibold text-[#102118]">URL hình ảnh</label>
                    <input id="image_url" name="image_url" type="url" required class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]" placeholder="https://images.unsplash.com/photo-...">
                </div>

                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]">
                    Cập nhật URL ảnh
                </button>
            </form>
        </article>

        <article class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Cách 2</p>
            <h3 class="mt-2 text-2xl font-extrabold text-[#102118]">Upload từ máy tính</h3>
            <p class="mt-2 text-sm text-[#6e8d7b]">Server sẽ kiểm tra đuôi file, MIME type, metadata ảnh và kích thước tối đa 5MB.</p>

            <form method="POST" enctype="multipart/form-data" action="upload_product_image.php" class="mt-6 space-y-4">
                <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">

                <div class="space-y-2">
                    <label for="product_id_upload" class="text-sm font-semibold text-[#102118]">Chọn sản phẩm</label>
                    <select id="product_id_upload" name="product_id_upload" required class="w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] focus:border-[#2e9b63] focus:ring-[#2e9b63]">
                        <option value="">Đang tải danh sách sản phẩm...</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label for="product_image" class="text-sm font-semibold text-[#102118]">Chọn file ảnh</label>
                    <input id="product_image" name="product_image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" required class="block w-full rounded-2xl border border-[#d9e9de] px-4 py-3 text-sm text-[#102118] file:mr-4 file:rounded-full file:border-0 file:bg-[#eef6f1] file:px-4 file:py-2 file:font-semibold file:text-[#2e9b63] hover:file:bg-[#e4f2ea]">
                </div>

                <div id="previewBox" class="hidden rounded-[1.25rem] border border-[#edf4ef] bg-[#f8fbf9] p-4">
                    <p class="text-sm font-semibold text-[#102118]">Xem trước</p>
                    <img id="previewImage" src="" alt="Preview upload" class="mt-3 h-48 w-full rounded-2xl object-cover">
                </div>

                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#102118] px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#1f3b2d]">
                    Upload và cập nhật
                </button>
            </form>
        </article>
    </section>

    <section class="rounded-[1.75rem] border border-[#d9e9de] bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#2e9b63]">Sản phẩm hiện tại</p>
                <h3 class="mt-2 text-2xl font-extrabold text-[#102118]">Danh sách để kiểm tra ảnh</h3>
            </div>
            <p class="text-sm text-[#6e8d7b]">Render client-side bằng DOM an toàn, không chèn HTML trực tiếp từ dữ liệu DB.</p>
        </div>

        <div id="productGrid" class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[1.25rem] border border-dashed border-[#d9e9de] px-4 py-8 text-center text-sm text-[#6e8d7b]">
                Đang tải sản phẩm...
            </div>
        </div>
    </section>
</div>

<script>
const productSelect = document.getElementById('product_id');
const uploadSelect = document.getElementById('product_id_upload');
const productGrid = document.getElementById('productGrid');
const fileInput = document.getElementById('product_image');
const previewBox = document.getElementById('previewBox');
const previewImage = document.getElementById('previewImage');

function formatCurrency(value) {
    return Number(value || 0).toLocaleString('vi-VN') + 'đ';
}

function resetProductOptions(select) {
    select.innerHTML = '';
    const option = document.createElement('option');
    option.value = '';
    option.textContent = '-- Chọn sản phẩm --';
    select.appendChild(option);
}

function appendProductOption(select, product) {
    const option = document.createElement('option');
    option.value = String(product.id);
    option.textContent = `${product.name} (${formatCurrency(product.price)})`;
    select.appendChild(option);
}

function buildProductCard(product) {
    const card = document.createElement('article');
    card.className = 'rounded-[1.25rem] border border-[#edf4ef] p-4';

    const image = document.createElement('img');
    image.src = product.thumbnail_url || 'https://via.placeholder.com/320x240?text=No+Image';
    image.alt = product.name;
    image.className = 'h-40 w-full rounded-2xl object-cover';
    image.addEventListener('error', () => {
        image.src = 'https://via.placeholder.com/320x240?text=No+Image';
    });

    const title = document.createElement('h4');
    title.className = 'mt-4 font-bold text-[#102118]';
    title.textContent = product.name;

    const price = document.createElement('p');
    price.className = 'mt-2 text-sm font-semibold text-[#2e9b63]';
    price.textContent = formatCurrency(product.price);

    const meta = document.createElement('p');
    meta.className = 'mt-1 text-xs text-[#6e8d7b]';
    meta.textContent = `ID: ${product.id}`;

    card.appendChild(image);
    card.appendChild(title);
    card.appendChild(price);
    card.appendChild(meta);
    return card;
}

async function loadProducts() {
    try {
        const response = await fetch('get_products.php', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error('Failed to load products');
        }

        const products = await response.json();

        resetProductOptions(productSelect);
        resetProductOptions(uploadSelect);
        productGrid.innerHTML = '';

        products.forEach((product) => {
            appendProductOption(productSelect, product);
            appendProductOption(uploadSelect, product);
            productGrid.appendChild(buildProductCard(product));
        });

        if (products.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'rounded-[1.25rem] border border-dashed border-[#d9e9de] px-4 py-8 text-center text-sm text-[#6e8d7b] sm:col-span-2 xl:col-span-4';
            empty.textContent = 'Chưa có sản phẩm nào để cập nhật ảnh.';
            productGrid.appendChild(empty);
        }
    } catch (_error) {
        productGrid.innerHTML = '';
        const errorCard = document.createElement('div');
        errorCard.className = 'rounded-[1.25rem] border border-red-200 bg-red-50 px-4 py-8 text-center text-sm text-red-700 sm:col-span-2 xl:col-span-4';
        errorCard.textContent = 'Không thể tải danh sách sản phẩm lúc này.';
        productGrid.appendChild(errorCard);
    }
}

fileInput?.addEventListener('change', (event) => {
    const file = event.target.files?.[0];
    if (!file) {
        previewBox.classList.add('hidden');
        previewImage.src = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = (loadEvent) => {
        previewImage.src = String(loadEvent.target?.result || '');
        previewBox.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
});

loadProducts();
</script>

<?php render_admin_footer(); ?>
