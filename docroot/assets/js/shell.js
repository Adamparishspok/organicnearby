function shellOnLoad() {
  if (window.pageOnLoad) return pageOnLoad();
}

function searchingDivHide(hide) {
  var searchingDiv = null;
  searchingDiv = document.getElementById("searchingDiv");
  if (!searchingDiv) return;

  if (hide) searchingDiv.classList.add("hideme");
  else searchingDiv.classList.remove("hideme");
}
