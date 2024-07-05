function confirmDeletion(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette idée')) {
        window.location.href = '../../database/idee/supprimer_idee.php?id=' + id;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const menuButton = document.querySelector('.menu-deroulant button');
    const menuList = document.querySelector('.menu-deroulant ul');

    menuButton.addEventListener('click', () => {
        menuList.style.display = menuList.style.display === 'flex' ? 'none' : 'flex';
    });

    menuButton.addEventListener('mouseover', () => {
        menuList.style.display = 'flex';
    });

    menuButton.addEventListener('mouseout', () => {
        if (menuList.style.display !== 'flex') {
            menuList.style.display = 'none';
        }
    });

    menuList.addEventListener('mouseover', () => {
        menuList.style.display = 'flex';
    });

    menuList.addEventListener('mouseout', () => {
        menuList.style.display = 'none';
    });
});