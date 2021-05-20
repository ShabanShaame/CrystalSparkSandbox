const helperIcon = document.getElementById('helperIcon');
const closeMenu = document.getElementById('closeMenu');
let open = false;

helperIcon.addEventListener('click', () => {
    openOrClose();
    scaleVanish();
})

closeMenu.addEventListener('click', ()=>{
    openOrClose();
    scaleAppear();
})

const scaleVanish = () => {

    anime({
        targets: '#helperIcon',
        scale: 0.2,
        duration: 800,
        easing: 'linear'
    })

    setTimeout(() => {
        helperIcon.style.display = 'none';
    }, 800)
}

const scaleAppear = () => {

    helperIcon.style.display = 'block';
    anime({
        targets: '#helperIcon',
        scale: 1,
        duration: 400,
        easing: 'linear'
    })
}


const openOrClose = () => {
    
    const translate = (open == false) ? 200 : -200;
    open = (translate == 200) ? true : false;

    anime({
        targets: '#helperContent',
        translateX: translate,
        duration: 800,
        easing: 'easeOutQuad'
    })
}


// $.noConflict();
jQuery(document).ready(function($){

    $('#cscDropDown').on('click', ()=>{
        $('#cscMenu').slideToggle(500);
    })

});