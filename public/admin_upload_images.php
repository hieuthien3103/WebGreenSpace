<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Ảnh Sản Phẩm - GreenSpace Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { 
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3ebd93;
        }
        .upload-area {
            border: 2px dashed #3ebd93;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background: #f8faf9;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            background: #e9f2ec;
            border-color: #2d8b6a;
        }
        .upload-area.dragover {
            background: #d2e4da;
            border-color: #2d8b6a;
        }
        .upload-icon {
            font-size: 48px;
            color: #3ebd93;
            margin-bottom: 10px;
        }
        input[type="file"] { display: none; }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        .btn-primary {
            background: #3ebd93;
            color: white;
        }
        .btn-primary:hover {
            background: #2d8b6a;
        }
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .product-item {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            background: white;
        }
        .product-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .product-item h4 {
            font-size: 14px;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        .product-item .price {
            color: #3ebd93;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .upload-btn-wrapper {
            position: relative;
            display: inline-block;
        }
        .preview-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .preview-item {
            position: relative;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }
        .preview-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .preview-item .remove {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255,0,0,0.8);
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            font-size: 16px;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        .form-group select,
        .form-group input[type="text"],
        .form-group input[type="url"] {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .instructions h3 {
            color: #856404;
            margin-bottom: 10px;
        }
        .instructions ol {
            margin-left: 20px;
            color: #856404;
        }
        .instructions li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌿 Quản lý Ảnh Sản Phẩm</h1>
        <p class="subtitle">Upload và quản lý hình ảnh cho sản phẩm trong database</p>

        <!-- Instructions -->
        <div class="instructions">
            <h3>📋 Hướng dẫn sử dụng:</h3>
            <ol>
                <li><strong>Cách 1 - Dùng URL từ Internet:</strong> Nhập URL ảnh từ Unsplash, Pexels, hoặc nguồn khác (miễn phí, nhanh)</li>
                <li><strong>Cách 2 - Upload ảnh từ máy:</strong> Kéo thả hoặc chọn ảnh từ máy tính (ảnh sẽ lưu vào folder uploads)</li>
                <li><strong>Cập nhật database:</strong> Sau khi upload, script tự động cập nhật đường dẫn ảnh vào database</li>
            </ol>
        </div>

        <!-- Method 1: URL Image -->
        <div class="card">
            <div class="section-title">📌 Cách 1: Cập nhật bằng URL</div>
            
            <form id="urlForm" method="POST" action="update_product_image.php">
                <div class="form-group">
                    <label>Chọn sản phẩm:</label>
                    <select name="product_id" id="product_select" required>
                        <option value="">-- Chọn sản phẩm --</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>URL hình ảnh:</label>
                    <input type="url" name="image_url" placeholder="https://images.unsplash.com/photo-..." required>
                    <small style="color: #7f8c8d; display: block; margin-top: 5px;">
                        💡 Gợi ý: Tìm ảnh miễn phí tại <a href="https://unsplash.com" target="_blank">Unsplash.com</a> hoặc <a href="https://pexels.com" target="_blank">Pexels.com</a>
                    </small>
                </div>
                
                <button type="submit" class="btn btn-primary">Cập nhật URL ảnh</button>
            </form>
        </div>

        <!-- Method 2: Upload from Computer -->
        <div class="card">
            <div class="section-title">📤 Cách 2: Upload ảnh từ máy tính</div>
            
            <form id="uploadForm" method="POST" enctype="multipart/form-data" action="upload_product_image.php">
                <div class="form-group">
                    <label>Chọn sản phẩm:</label>
                    <select name="product_id_upload" required>
                        <option value="">-- Chọn sản phẩm --</option>
                    </select>
                </div>
                
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">📁</div>
                    <h3>Kéo thả ảnh vào đây</h3>
                    <p>hoặc click để chọn file</p>
                    <input type="file" id="fileInput" name="product_image" accept="image/*" required>
                </div>
                
                <div class="preview-images" id="previewImages"></div>
                
                <button type="submit" class="btn btn-primary" style="margin-top: 15px;">Upload và cập nhật</button>
            </form>
        </div>

        <!-- Current Products -->
        <div class="card">
            <div class="section-title">🌱 Sản phẩm hiện tại</div>
            <div class="product-grid" id="productGrid">
                <p>Đang tải sản phẩm...</p>
            </div>
        </div>
    </div>

    <script>
        // Load products
        async function loadProducts() {
            try {
                const response = await fetch('get_products.php');
                const products = await response.json();
                
                // Populate select boxes
                const selects = document.querySelectorAll('select[name="product_id"], select[name="product_id_upload"]');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">-- Chọn sản phẩm --</option>';
                    products.forEach(product => {
                        select.innerHTML += `<option value="${product.id}">${product.name} (${product.price}đ)</option>`;
                    });
                });
                
                // Display products
                const grid = document.getElementById('productGrid');
                grid.innerHTML = products.map(product => `
                    <div class="product-item">
                        <img src="${product.thumbnail_url || 'https://via.placeholder.com/200x150?text=No+Image'}" 
                             alt="${product.name}"
                             onerror="this.src='https://via.placeholder.com/200x150?text=No+Image'">
                        <h4>${product.name}</h4>
                        <div class="price">${parseInt(product.price).toLocaleString('vi-VN')}đ</div>
                        <small style="color: #7f8c8d;">ID: ${product.id}</small>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading products:', error);
            }
        }

        // Drag and drop
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');

        uploadArea.addEventListener('click', () => fileInput.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                previewImage(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                previewImage(e.target.files[0]);
            }
        });

        function previewImage(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('previewImages').innerHTML = `
                    <div class="preview-item">
                        <img src="${e.target.result}" alt="Preview">
                        <button class="remove" onclick="clearPreview()">×</button>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }

        function clearPreview() {
            document.getElementById('previewImages').innerHTML = '';
            fileInput.value = '';
        }

        // Load products on page load
        loadProducts();
    </script>
</body>
</html>
