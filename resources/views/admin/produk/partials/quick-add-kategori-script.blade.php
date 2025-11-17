<script>
    document.getElementById('simpan-kategori-cepat').addEventListener('click', async function(e) {
        e.preventDefault(); 
        const namaKategoriInput = document.getElementById('modal_nama_kategori');
        const errorMessage = document.getElementById('modal-error-kategori');
        const dropdown = document.getElementById('id_kategori_dropdown');
        const batalButton = document.getElementById('batal-kategori-cepat');
        
        errorMessage.textContent = ''; 
        errorMessage.classList.remove('text-green-600', 'text-red-600');

        try {
            const response = await fetch("{{ route('admin.kategori.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ nama_kategori: namaKategoriInput.value })
            });
            const data = await response.json();
            if (!response.ok) {
                errorMessage.classList.add('text-red-600');
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
                    batalButton.click();
                    errorMessage.textContent = ''; 
                    errorMessage.classList.remove('text-green-600');
                }, 1000); 
            }
        } catch (error) {
            console.error('Error Kategori:', error);
            errorMessage.textContent = 'Gagal terhubung ke server (Script Error).';
        }
    });
</script>