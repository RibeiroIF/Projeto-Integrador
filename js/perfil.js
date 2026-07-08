function atualizarFotoInstantanea(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const imgPreview = document.getElementById('img-perfil-preview');
            const letraPreview = document.getElementById('avatar-letra-preview');
            
            if (imgPreview) {
                imgPreview.src = e.target.result;
            } else if (letraPreview) {
                const novaImg = document.createElement('img');
                novaImg.id = 'img-perfil-preview';
                novaImg.src = e.target.result;
                novaImg.className = 'perfil-avatar-img';
                letraPreview.parentNode.replaceChild(novaImg, letraPreview);
            }
            
            const avatarGrande = document.querySelector('.avatar-grande');
            if (avatarGrande) {
                avatarGrande.innerHTML = `<img src="${e.target.result}" alt="Foto" class="perfil-aside-avatar-img">`;
            }
        }
        
        reader.readAsDataURL(input.files[0]);
        
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
