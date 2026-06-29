function atualizarFotoInstantanea(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // 1. Atualiza a foto dentro da tela de perfil (Miniatura)
            const imgPreview = document.getElementById('img-perfil-preview');
            const letraPreview = document.getElementById('avatar-letra-preview');
            
            if (imgPreview) {
                imgPreview.src = e.target.result;
            } else if (letraPreview) {
                const novaImg = document.createElement('img');
                novaImg.id = 'img-perfil-preview';
                novaImg.src = e.target.result;
                novaImg.style = "width: 100%; height: 100%; object-fit: cover; border-radius: 50%;";
                letraPreview.parentNode.replaceChild(novaImg, letraPreview);
            }
            
            // 2. Atualiza instantaneamente a foto do Bloco Direito (aside)
            const avatarGrande = document.querySelector('.avatar-grande');
            if (avatarGrande) {
                avatarGrande.innerHTML = `<img src="${e.target.result}" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">`;
            }
        }
        
        reader.readAsDataURL(input.files[0]);
        
        // 3. Envia para o iframe invisível salvar no banco em background (sem mexer na tela)
        input.form.submit();
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tela') === 'perfil') {
        if (typeof navegar === 'function') {
            navegar('tela-perfil');
        }
    }
});