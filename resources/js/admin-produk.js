// Cek jika kita berada di halaman 'create' atau 'edit' produk
if (document.getElementById('simpan-kategori-cepat')) {
    
    // --- SCRIPT UNTUK MODAL KATEGORI ---
    document.getElementById('simpan-kategori-cepat').addEventListener('click', async function(e) {
        e.preventDefault(); 
        const namaKategoriInput = document.getElementById('modal_nama_kategori');
        const errorMessage = document.getElementById('modal-error-kategori');
        const dropdown = document.getElementById('id_kategori_dropdown');
        const modal = this.closest('[x-data]'); 
        errorMessage.textContent = ''; 
        errorMessage.classList.remove('text-green-600', 'text-red-600');

        try {
            // Ambil token CSRF dari tag meta
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            const response = await fetch("/admin/kategori", { // Kita hardcode URL-nya
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ nama_kategori: namaKategoriInput.value })
            });
            const data = await response.json();
            if (!response.ok) {
                if (data.errors && data.errors.nama_kategori) {
                    errorMessage.textContent = data.errors.nama_kategori[0];
                } else { errorMessage.textContent = data.message || 'Terjadi kesalahan.'; }
            } else if (data.success) {
                const newOption = new Option(data.kategori.nama_kategori, data.kategori.id_kategori, true, true);
                dropdown.add(newOption);
                namaKategoriInput.value = ''; 
                
                errorMessage.textContent = 'Berhasil disimpan!';
                errorMessage.classList.add('text-green-600');
                setTimeout(() => {
                    modal.__x.$data.modalOpen = false; // Tutup modal
                    errorMessage.textContent = ''; 
                    errorMessage.classList.remove('text-green-600');
                }, 1000); 
            }
        } catch (error) {
            console.error('Error Kategori:', error);
            errorMessage.textContent = 'Gagal terhubung ke server (Script Error).';
        }
    });
}

// Cek jika kita berada di halaman 'create' atau 'edit' produk
if (document.getElementById('simpan-supplier-cepat')) {

    // --- SCRIPT UNTUK MODAL SUPPLIER ---
    document.getElementById('simpan-supplier-cepat').addEventListener('click', async function(e) {
        e.preventDefault(); 
        const namaSupplierInput = document.getElementById('modal_nama_supplier');
        const errorMessage = document.getElementById('modal-error-supplier');
        const dropdown = document.getElementById('id_supplier_dropdown');
        const modal = this.closest('[x-data]'); 
        errorMessage.textContent = ''; 
        errorMessage.classList.remove('text-green-600', 'text-red-600');

        try {
            // Ambil token CSRF dari tag meta
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            const response = await fetch("/admin/supplier", { // Kita hardcode URL-nya
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ nama_supplier: namaSupplierInput.value })
            });
            const data = await response.json();
            if (!response.ok) {
                if (data.errors && data.errors.nama_supplier) {
                    errorMessage.textContent = data.errors.nama_supplier[0];
                } else { errorMessage.textContent = data.message || 'Terjadi kesalahan.'; }
            } else if (data.success) {
                const newOption = new Option(data.supplier.nama_supplier, data.supplier.id_supplier, true, true);
                dropdown.add(newOption);
                namaSupplierInput.value = ''; 
                
                errorMessage.textContent = 'Berhasil disimpan!';
                errorMessage.classList.add('text-green-600');
                setTimeout(() => {
                    modal.__x.$data.modalOpen = false; // Tutup modal
                    errorMessage.textContent = ''; 
                    errorMessage.classList.remove('text-green-600');
                }, 1000); 
            }
        } catch (error) {
            console.error('Error Supplier:', error);
            errorMessage.textContent = 'Gagal terhubung ke server (Script Error).';
        }
    });
}