Bcl4Sidebar = {
    init : function() {
        Osynapsy.element('body').on('click', '.bcl4-sidebar-command', function(){
            let sidebar = document.getElementById(this.dataset.target);
            if (Osynapsy.isEmpty(sidebar)) {
                console.log('Sidebar not found');
                return;
            }
            sidebar.classList.toggle('d-none');
            return;
            let widthSidebar = sidebar.dataset.isOpen === '0' ? '250px' : '0px';
            sidebar.style.width = widthSidebar;
            console.log(sidebar.nextSibling);
            sidebar.nextElementSibling.style.marginLeft = widthSidebar;
            sidebar.dataset.isOpen = sidebar.dataset.isOpen === '0' ? '1' : '0';
        });
    }
};

if (window.Osynapsy) {
    Osynapsy.plugin.register('Bcl4Sidebar',function(){
        Bcl4Sidebar.init();
    });
}