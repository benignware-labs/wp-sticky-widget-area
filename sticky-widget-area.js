import { ResizeSensor } from 'css-element-queries';
import StickySidebar from 'sticky-sidebar';

let sidebar;

window.ResizeSensor = ResizeSensor;

const readyHandler = (event) => {
  if (sidebar) {
    sidebar.destroy();
    sidebar = null;
  }

  const sidebarElement = document.querySelector('*[data-sticky-widget-area-role="sidebar"]');
  const containerElement = document.querySelector('*[data-sticky-widget-area-role="container"]');
  const sidebarInnerElement = document.querySelector('*[data-sticky-widget-area-role="sidebar-inner"]');

  if (sidebarElement && containerElement) {
    const optionsEncoded = sidebarElement.getAttribute('data-sticky-widget-area-options');
    const optionsDecoded = JSON.parse(decodeURIComponent(optionsEncoded));
    const options = {
      //resizeSensor: true,
      topSpacing: 0,
      bottomSpacing: 0,
      containerSelector: '*[data-sticky-widget-area-role="container"]',
      innerWrapperSelector: '*[data-sticky-widget-area-role="sidebar-inner"]',
      minWidth: 992, // TODO: Get bootstrap breakpoints dynamically
      ...optionsDecoded
    };

    sidebar = new StickySidebar(sidebarElement, options);
  }
}

document.addEventListener('turbolinks:load', readyHandler);
document.addEventListener('DOMContentLoaded', readyHandler);
