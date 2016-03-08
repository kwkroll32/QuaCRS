/*
  @author Taha Mazher Topiwala
*/

var navBar = _("navigationbar");
var projectName = _("projectName");
window.onscroll = function() {adjustNavBar()};

function adjustNavBar() {
  if(document.body.scrollTop > 40){
    navBar.classList.add('navigationBarShrunk');
  }else{
    navBar.classList.remove('navigationBarShrunk');
  }
}
