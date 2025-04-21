function loadClickEventForTabs(){
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.codespreader-tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            button.classList.add('active');
            document.getElementById(button.dataset.tab).classList.add('active');
        });
    });
}

window.onload = (event) => {
    setTimeout(function(){
        const tabsContainerCustomCode = document.querySelectorAll('.codespreader-custom-code');

        tabsContainerCustomCode.forEach(el => {
            el.classList.add('codespreader-tab-content');
        });
        loadClickEventForTabs();
    }, 200);
    
};