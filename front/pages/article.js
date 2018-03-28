export default () => {

    const inputNewsLetter = find(document, '.newsletter input');
    let isExpanded;
    let isFixed;
    let isFixedToBottom;
    const socialBlock = find(document, '.article__social');
    const topSocialBlock = socialBlock.getBoundingClientRect().top;
    const article = find(document, '.article__main');
    const botArticle = article.getBoundingClientRect().bottom;

    function expandInput(e) {
        if (e.target == inputNewsLetter && !isExpanded){
            addClass(inputNewsLetter, 'isExpanded');
            isExpanded = true;}
        else if (e.target != inputNewsLetter && isExpanded){
            removeClass(inputNewsLetter, 'isExpanded');
            isExpanded = false;
        }
    };
    on(document,'click', expandInput)

    function socialToFixed() {
        if ((window.scrollY >= topSocialBlock) && (window.scrollY < (botArticle - 600)) && !isFixedToBottom){
            addClass(socialBlock, 'isFixed')
            isFixed = true;
            console.log('POSITION FIX')
        }
        else if (isFixed  &&  (window.scrollY < topSocialBlock)) {
            removeClass(socialBlock, 'isFixed')
            console.log('POSITION  RELATIVE')
        }
        else if (isFixed  &&  (window.scrollY > (botArticle - 600))) {
            removeClass(socialBlock, 'isFixed')
            addClass(socialBlock, 'isFixedToBottom')
            isFixedToBottom = true;
            console.log('POSITION FIX BOTTOM')
        }
        else if (isFixedToBottom && (window.scrollY < (botArticle - 600))){
            addClass(socialBlock, 'isFixed')
            removeClass(socialBlock, 'isFixedToBottom')
            isFixedToBottom = false;
            isFixed = true;
            console.log('REMOVE POSITION FIX BOTTOM & ADD POSITION FIX')
        }
    }
    on(document, 'scroll', socialToFixed)
};
