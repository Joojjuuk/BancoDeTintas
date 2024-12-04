function mostrarFormulario() {
    const tipoPessoa = document.querySelector('input[name="tipo"]:checked');
    const formFisica = document.getElementById('form-fisica');
    const formJuridica = document.getElementById('form-juridica');
    
    formFisica.classList.add('hidden');
    formJuridica.classList.add('hidden');
    
    if (tipoPessoa) {
        if (tipoPessoa.value === 'fisica') {
            formFisica.classList.remove('hidden');
        } else {
            formJuridica.classList.remove('hidden');
        }
    }
}

document.addEventListener("DOMContentLoaded", function() {
    mostrarFormulario();
});