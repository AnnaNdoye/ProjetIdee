document.querySelectorAll('.formulaire input, .formulaire select').forEach(input => 
{
    input.addEventListener('focus', () => {
        input.parentNode.querySelector('i').style.color = '#FF6600';
    });

    input.addEventListener('blur', () => {
        input.parentNode.querySelector('i').style.color = '#999';
    });
});

document.querySelector('.logo').addEventListener('click', () => 
{
    window.location.href = 'accueil.html';
});

