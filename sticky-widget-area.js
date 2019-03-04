import { ResizeSensor } from 'css-element-queries';
import StickySidebar from 'sticky-sidebar';

let sidebar;

window.ResizeSensor = ResizeSensor;

const updateSticky = () => {
  if (sidebar) {
    sidebar.updateSticky();
    window.requestAnimationFrame(() => {
      sidebar.updateSticky();
    });
  }
}

const readyHandler = (event) => {
  if (sidebar) {
    sidebar.destroy();
    sidebar = null;
  }

  const sidebarElement = document.querySelector('*[data-sticky-widget-area-role="sidebar"]');
  const containerElement = document.querySelector('*[data-sticky-widget-area-role="container"]');
  const sidebarInnerElement = document.querySelector('*[data-sticky-widget-area-role="sidebar-inner"]');

  if (sidebarElement && containerElement) {
    const adminBar = document.querySelector('#wpadminbar');

    const optionsEncoded = sidebarElement.getAttribute('data-sticky-widget-area-options');
    const optionsDecoded = { ...JSON.parse(decodeURIComponent(optionsEncoded)) };

    let topSpacing = 0;

    if (adminBar) {
      topSpacing+= adminBar.getBoundingClientRect().height;
    }

    topSpacing+= optionsDecoded.topSpacing ? optionsDecoded.topSpacing : 0;

    const options = {
      resizeSensor: true,
      topSpacing: 0,
      bottomSpacing: 0,
      containerSelector: '*[data-sticky-widget-area-role="container"]',
      innerWrapperSelector: '*[data-sticky-widget-area-role="sidebar-inner"]',
      minWidth: 768,
      ...optionsDecoded,
      topSpacing: topSpacing
    };

    console.log('sidebar options', options);

    sidebar = new StickySidebar(sidebarElement, options);

    window.requestAnimationFrame(updateSticky);
  }
}

document.addEventListener('turbolinks:load', readyHandler);
document.addEventListener('DOMContentLoaded', readyHandler);
window.addEventListener('resize', updateSticky);
