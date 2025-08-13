<template>
  <div class="hierarchy-container">
    <div ref="svgContainer" class="svg-wrapper">
      <!-- SVG akan dirender oleh D3 -->
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import * as d3 from 'd3'

const props = defineProps({
  app: Object
})
const emit = defineEmits(['node-click'])

const svgContainer = ref(null)
let svg = null

// Fungsi untuk mengukur lebar teks (dengan font default)
function getTextWidth(text, font = '16px Arial') {
  const canvas = getTextWidth.canvas || (getTextWidth.canvas = document.createElement('canvas'));
  const context = canvas.getContext('2d');
  context.font = font;
  return context.measureText(text).width;
}

const minNodeWidth = 180; // Lebih lebar untuk teks yang lebih panjang
const nodeHeight = 50; // Sedikit lebih tinggi
const nodeGapX = 100; // Jarak antar kolom lebih besar
const nodeGapY = 50; // Jarak vertikal lebih besar
const margin = { top: 60, left: 60, right: 60, bottom: 60 }; // Margin lebih besar

function getDiagramData(app) {
  if (!app || !app.projects) return { nodes: [], links: [], columnLayout: {} };

  // === DEBUG: Struktur Data App ===
  console.log('=== DEBUG: Struktur Data App ===');
  console.log('App:', app);
  console.log('Projects:', app.projects);
  
  app.projects.forEach((project, pIdx) => {
    console.log(`Project ${pIdx}:`, project);
    
    if (project.deployments && project.deployments.length > 0) {
      project.deployments.forEach((deployment, dIdx) => {
        console.log(`  Deployment ${dIdx}:`, deployment);
        console.log(`    WAFs:`, deployment.wafs);
        
        if (deployment.wafs && deployment.wafs.length > 0) {
          deployment.wafs.forEach((waf, wIdx) => {
            console.log(`      WAF ${wIdx}:`, waf);
            console.log(`        WAF ID:`, waf.id);
            console.log(`        WAF IP:`, waf.ip_waf);
            console.log(`        WAF ID Deployment:`, waf.id_deployment);
            console.log(`        IP Publiks:`, waf.ippubliks);
            console.log(`        IP Publiks type:`, typeof waf.ippubliks);
            console.log(`        IP Publiks length:`, waf.ippubliks ? waf.ippubliks.length : 'undefined');
            
            // Cek apakah ada property lain yang mungkin berisi IP Publik
            console.log(`        WAF keys:`, Object.keys(waf));
            console.log(`        WAF values:`, Object.values(waf));
            
            // Coba akses dengan cara yang berbeda
            console.log(`        waf.ippubliks:`, waf.ippubliks);
            console.log(`        waf.ip_publiks:`, waf.ip_publiks);
            console.log(`        waf['ippubliks']:`, waf['ippubliks']);
            console.log(`        waf['ip_publiks']:`, waf['ip_publiks']);
            
            // Debug lebih detail untuk melihat struktur data
            console.log(`        WAF raw data:`, JSON.stringify(waf, null, 2));
            
            // Test apakah ada data IP Publik dengan berbagai cara akses
            const possibleIpPublikKeys = ['ippubliks', 'ip_publiks', 'ip_publik', 'ipPublik'];
            possibleIpPublikKeys.forEach(key => {
              console.log(`        waf.${key}:`, waf[key]);
            });
            
            // Cek apakah ada data IP Publik yang tersimpan
            if (waf.ippubliks && waf.ippubliks.length > 0) {
              waf.ippubliks.forEach((ipPublik, ipIdx) => {
                console.log(`          IP Publik ${ipIdx}:`, ipPublik);
                console.log(`            Inpervas:`, ipPublik.inpervas);
                
                if (ipPublik.inpervas && ipPublik.inpervas.length > 0) {
                  ipPublik.inpervas.forEach((inperva, inpIdx) => {
                    console.log(`              Inperva ${inpIdx}:`, inperva);
                    console.log(`                DNS:`, inperva.dns);
                    
                    if (inperva.dns && inperva.dns.length > 0) {
                      inperva.dns.forEach((dns, dnsIdx) => {
                        console.log(`                  DNS ${dnsIdx}:`, dns);
                      });
                    }
                  });
                }
              });
            }
          });
        }
        
        // Debug Service API Gates
        const services = deployment.serviceApiGates || deployment.service_api_gates || [];
        console.log(`    Service API Gates:`, services);
        
        if (services.length > 0) {
          services.forEach((service, sIdx) => {
            console.log(`      Service ${sIdx}:`, service);
            console.log(`        Route API Gates:`, service.routeApiGates || service.route_api_gates);
          });
        }
      });
    } else {
      console.log(`  Project ${pIdx} tidak memiliki deployments`);
    }
  });
  console.log('=== END DEBUG ===');

  // --- Pre-calculate all node widths and max width per column ---
  const appLabel = app.nama_singkat_proses_bisnis;
  const maxAppWidth = Math.max(minNodeWidth, getTextWidth(appLabel) + 32);

  const projectWidths = app.projects.map(p => Math.max(minNodeWidth, getTextWidth(p.project_name) + 32));
  const maxProjectWidth = projectWidths.length > 0 ? Math.max(...projectWidths) : minNodeWidth;

  const deploymentLabels = [];
  app.projects.forEach(p => {
    (p.deployments || []).forEach(d => {
      let label = 'Deployment';
      if (d.service_name) label = `${d.service_name}`;
      else if (d.service_name) label = d.service_name;
      deploymentLabels.push(label);
    });
  });
  const deploymentWidths = deploymentLabels.map(label => Math.max(minNodeWidth, getTextWidth(label) + 32));
  const maxDeploymentWidth = deploymentWidths.length > 0 ? Math.max(...deploymentWidths) : minNodeWidth;

  const serviceLabels = [];
  app.projects.forEach(p => {
    (p.deployments || []).forEach(d => {
      const services = d.serviceApiGates || d.service_api_gates || [];
      services.forEach(s => {
        serviceLabels.push(s.service_name || 'Service Tanpa Nama');
      });
    });
  });
  const serviceWidths = serviceLabels.map(label => Math.max(minNodeWidth, getTextWidth(label) + 32));
  const maxServiceWidth = serviceWidths.length > 0 ? Math.max(...serviceWidths) : minNodeWidth;

  // --- Pre-calculate WAF, IP Publik, Inperva, DNS widths ---
  const wafLabels = [];
  const ipPublikLabels = [];
  const inpervaLabels = [];
  const dnsLabels = [];
  
  app.projects.forEach(p => {
    (p.deployments || []).forEach(d => {
      // WAF
      if (d.wafs && d.wafs.length > 0) {
        d.wafs.forEach(w => {
          wafLabels.push(w.ip_waf || 'WAF');
        });
      }
      
      // IP Publik
      if (d.wafs) {
        d.wafs.forEach(w => {
          if (w.ippubliks && w.ippubliks.length > 0) {
            w.ippubliks.forEach(ip => {
              ipPublikLabels.push(ip.ip_publik || 'IP Publik');
            });
          }
        });
      }
      
      // Inperva
      if (d.wafs) {
        d.wafs.forEach(w => {
          if (w.ippubliks) {
            w.ippubliks.forEach(ip => {
              if (ip.inpervas && ip.inpervas.length > 0) {
                ip.inpervas.forEach(inp => {
                  inpervaLabels.push(inp.name || 'Inperva');
                });
              }
            });
          }
        });
      }
      
      // DNS
      if (d.wafs) {
        d.wafs.forEach(w => {
          if (w.ippubliks) {
            w.ippubliks.forEach(ip => {
              if (ip.inpervas) {
                ip.inpervas.forEach(inp => {
                  if (inp.dns && inp.dns.length > 0) {
                    inp.dns.forEach(dns => {
                      dnsLabels.push(dns.name && dns.type ? `${dns.name} ${dns.type}` : dns.name || 'DNS');
                    });
                  }
                });
              }
            });
          }
        });
      }
    });
  });

  const wafWidths = wafLabels.map(label => Math.max(minNodeWidth, getTextWidth(label) + 32));
  const maxWafWidth = wafWidths.length > 0 ? Math.max(...wafWidths) : minNodeWidth;

  const ipPublikWidths = ipPublikLabels.map(label => Math.max(minNodeWidth, getTextWidth(label) + 32));
  const maxIpPublikWidth = ipPublikWidths.length > 0 ? Math.max(...ipPublikWidths) : minNodeWidth;

  const inpervaWidths = inpervaLabels.map(label => Math.max(minNodeWidth, getTextWidth(label) + 32));
  const maxInpervaWidth = inpervaWidths.length > 0 ? Math.max(...inpervaWidths) : minNodeWidth;

  const dnsWidths = dnsLabels.map(label => Math.max(minNodeWidth, getTextWidth(label) + 32));
  const maxDnsWidth = dnsWidths.length > 0 ? Math.max(...dnsWidths) : minNodeWidth;

  // --- Define fixed X positions for each column ---
  const appColumnX = margin.left;
  const projectColumnX = appColumnX + maxAppWidth + nodeGapX;
  const deploymentColumnX = projectColumnX + maxProjectWidth + nodeGapX;
  const serviceColumnX = deploymentColumnX + maxDeploymentWidth + nodeGapX;
  // WAF, IP Publik, Inperva, DNS tengah node mereka berada di tengah kolom deployment
  const wafColumnX = deploymentColumnX + (maxDeploymentWidth - maxWafWidth) / 2; // Tengah node WAF di tengah kolom deployment
  const ipPublikColumnX = deploymentColumnX + (maxDeploymentWidth - maxIpPublikWidth) / 2; // Tengah node IP Publik di tengah kolom deployment
  const inpervaColumnX = deploymentColumnX + (maxDeploymentWidth - maxInpervaWidth) / 2; // Tengah node Inperva di tengah kolom deployment
  const dnsColumnX = deploymentColumnX + (maxDeploymentWidth - maxDnsWidth) / 2; // Tengah node DNS di tengah kolom deployment

  const columnLayout = {
    app: { x: appColumnX, width: maxAppWidth },
    project: { x: projectColumnX, width: maxProjectWidth },
    deployment: { x: deploymentColumnX, width: maxDeploymentWidth },
    service: { x: serviceColumnX, width: maxServiceWidth },
    waf: { x: wafColumnX, width: maxWafWidth },
    ipPublik: { x: ipPublikColumnX, width: maxIpPublikWidth },
    inperva: { x: inpervaColumnX, width: maxInpervaWidth },
    dns: { x: dnsColumnX, width: maxDnsWidth }
  };


  const nodes = [];
  const links = [];

  // Jika tidak ada project, tampilkan App node di tengah
  if (app.projects.length === 0) {
    nodes.push({
      id: 'app',
      label: appLabel,
      type: 'app',
      data: app,
      x: appColumnX,
      y: (window.innerHeight / 2) - (nodeHeight / 2),
      width: maxAppWidth
    });
    return { nodes, links, columnLayout };
  }

  let y = margin.top;
  // --- Hitung posisi Y dinamis agar tidak overlap ---
  let totalRows = 0;
  const projectRows = [];
  app.projects.forEach((project) => {
    let rowCount = 1;
    if (project.deployments && project.deployments.length > 0) {
      rowCount = 0;
      project.deployments.forEach((deployment) => {
        if (deployment.serviceApiGates && deployment.serviceApiGates.length > 0) {
          rowCount += deployment.serviceApiGates.length;
        } else {
          rowCount += 1;
        }
      });
    }
    projectRows.push(rowCount);
    totalRows += rowCount;
  });

  // --- Penempatan node ---
  let currentY = margin.top;
  // App node (diletakkan di tengah vertikal)
  nodes.push({
    id: 'app',
    label: appLabel,
    type: 'app',
    data: app,
    x: appColumnX,
    y: margin.top + (totalRows * (nodeHeight + nodeGapY) - nodeGapY) / 2 - nodeHeight / 2,
    width: maxAppWidth
  });

  app.projects.forEach((project, pIdx) => {
    const projectId = `project-${project.id}`;
    const projectNodeWidth = projectWidths[pIdx];
    // Project node
    let projectChildRows = projectRows[pIdx];
    let projectY = currentY + (projectChildRows * (nodeHeight + nodeGapY) - nodeGapY) / 2 - nodeHeight / 2;
    nodes.push({
      id: projectId,
      label: project.project_name,
      type: 'project',
      data: project,
      x: projectColumnX,
      y: projectY,
      width: projectNodeWidth
    });
    links.push({ source: 'app', target: projectId });

    // Deployments & Services
    if (project.deployments && project.deployments.length > 0) {
      let deploymentY = currentY;
      project.deployments.forEach((deployment, dIdx) => {
        const deploymentLabel = deploymentLabels[dIdx] || 'Deployment'; // Use pre-calculated label
        const deploymentNodeWidth = deploymentWidths[dIdx] || minNodeWidth; // Use pre-calculated width

        // Jika ada serviceApiGates, buat node service di bawah deployment
        if (
          (deployment.serviceApiGates && deployment.serviceApiGates.length > 0) ||
          (deployment.service_api_gates && deployment.service_api_gates.length > 0)
        ) {
          const serviceApiGates = deployment.serviceApiGates || deployment.service_api_gates;
          let serviceY = deploymentY;
          serviceApiGates.forEach((service) => {
            const serviceLabel = service.service_name || 'Service Tanpa Nama';
            const serviceNodeWidth = Math.max(minNodeWidth, getTextWidth(serviceLabel) + 32);
            nodes.push({
              id: `service-${service.id}`,
              label: serviceLabel,
              type: 'service',
              data: { ...service, routeApiGates: service.route_api_gates || service.routeApiGates || [] },
              x: serviceColumnX,
              y: serviceY,
              width: serviceNodeWidth
            });
            // Deployment node untuk service (invisible, hanya untuk edge)
            nodes.push({
              id: `deployment-${deployment.id}-service-${service.id}`,
              label: deploymentLabel,
              type: 'deployment',
              data: { ...deployment, currentService: service }, // Tambahkan info service yang sedang diproses
              x: deploymentColumnX,
              y: serviceY,
              width: deploymentNodeWidth
            });
            links.push({ source: projectId, target: `deployment-${deployment.id}-service-${service.id}` });
            links.push({ source: `deployment-${deployment.id}-service-${service.id}`, target: `service-${service.id}` });
            
            // Tambahkan WAF, IP Publik, Inperva, DNS untuk service ini
            if (deployment.wafs && deployment.wafs.length > 0) {
              deployment.wafs.forEach((waf, wIdx) => {
                const wafLabel = waf.ip_waf || 'WAF'; // Label menampilkan IP address
                const wafNodeWidth = Math.max(minNodeWidth, getTextWidth(wafLabel) + 32);
                
                nodes.push({
                  id: `waf-${waf.id}-service-${service.id}`,
                  label: wafLabel,
                  type: 'waf',
                  data: waf,
                  x: wafColumnX,
                  y: serviceY + nodeHeight + nodeGapY, // Posisi Y di bawah service
                  width: wafNodeWidth
                });
                links.push({ source: `service-${service.id}`, target: `waf-${waf.id}-service-${service.id}` });
                
                // IP Publik - posisi Y di bawah WAF
                if (waf.ippubliks && waf.ippubliks.length > 0) {
                  waf.ippubliks.forEach((ipPublik, ipIdx) => {
                    const ipLabel = ipPublik.ip_publik || 'IP Publik'; // Label menampilkan IP address
                    const ipNodeWidth = Math.max(minNodeWidth, getTextWidth(ipLabel) + 32);
                    
                    nodes.push({
                      id: `ip-${ipPublik.id}-waf-${waf.id}-service-${service.id}`,
                      label: ipLabel,
                      type: 'ipPublik',
                      data: ipPublik,
                      x: ipPublikColumnX,
                      y: serviceY + 2 * (nodeHeight + nodeGapY), // Posisi Y di bawah WAF
                      width: ipNodeWidth
                    });
                    links.push({ source: `waf-${waf.id}-service-${service.id}`, target: `ip-${ipPublik.id}-waf-${waf.id}-service-${service.id}` });
                    
                    // Inperva - posisi Y di bawah IP Publik
                    if (ipPublik.inpervas && ipPublik.inpervas.length > 0) {
                      ipPublik.inpervas.forEach((inperva, inpIdx) => {
                        const inpLabel = inperva.name || 'Inperva';
                        const inpNodeWidth = Math.max(minNodeWidth, getTextWidth(inpLabel) + 32);
                        
                        nodes.push({
                          id: `inperva-${inperva.id}-ip-${ipPublik.id}-waf-${waf.id}-service-${service.id}`,
                          label: inpLabel,
                          type: 'inperva',
                          data: inperva,
                          x: inpervaColumnX,
                          y: serviceY + 3 * (nodeHeight + nodeGapY), // Posisi Y di bawah IP Publik
                          width: inpNodeWidth
                        });
                        links.push({ source: `ip-${ipPublik.id}-waf-${waf.id}-service-${service.id}`, target: `inperva-${inperva.id}-ip-${ipPublik.id}-waf-${waf.id}-service-${service.id}` });
                        
                        // DNS - posisi Y di bawah Inperva
                        if (inperva.dns && inperva.dns.length > 0) {
                          inperva.dns.forEach((dns, dnsIdx) => {
                            const dnsLabel = dns.name && dns.type ? `${dns.name} ${dns.type}` : dns.name || 'DNS'; // Label menampilkan nama dan type
                            const dnsNodeWidth = Math.max(minNodeWidth, getTextWidth(dnsLabel) + 32);
                            
                            nodes.push({
                              id: `dns-${dns.id}-inperva-${inperva.id}-ip-${ipPublik.id}-waf-${waf.id}-service-${service.id}`,
                              label: dnsLabel,
                              type: 'dns',
                              data: dns,
                              x: dnsColumnX,
                              y: serviceY + 4 * (nodeHeight + nodeGapY), // Posisi Y di bawah Inperva
                              width: dnsNodeWidth
                            });
                            links.push({ source: `inperva-${inperva.id}-ip-${ipPublik.id}-waf-${waf.id}-service-${service.id}`, target: `dns-${dns.id}-inperva-${inperva.id}-ip-${ipPublik.id}-waf-${waf.id}-service-${service.id}` });
                          });
                        }
                      });
                    }
                  });
                }
              });
            }
            
            serviceY += nodeHeight + nodeGapY;
          });
          deploymentY = serviceY;
        } else {
          // Deployment tanpa service
          nodes.push({
            id: `deployment-${deployment.id}`,
            label: deploymentLabel,
            type: 'deployment',
            data: deployment,
            x: deploymentColumnX,
            y: deploymentY,
            width: deploymentNodeWidth
          });
          links.push({ source: projectId, target: `deployment-${deployment.id}` });
          
          // Tambahkan WAF, IP Publik, Inperva, DNS untuk deployment tanpa service
          if (deployment.wafs && deployment.wafs.length > 0) {
            deployment.wafs.forEach((waf, wIdx) => {
              const wafLabel = waf.ip_waf || 'WAF'; // Label menampilkan IP address
              const wafNodeWidth = Math.max(minNodeWidth, getTextWidth(wafLabel) + 32);
              
              nodes.push({
                id: `waf-${waf.id}-deployment-${deployment.id}`,
                label: wafLabel,
                type: 'waf',
                data: waf,
                x: wafColumnX,
                y: deploymentY + nodeHeight + nodeGapY, // Posisi Y di bawah deployment
                width: wafNodeWidth
              });
              links.push({ source: `deployment-${deployment.id}`, target: `waf-${waf.id}-deployment-${deployment.id}` });
              
              // IP Publik - posisi Y di bawah WAF
              if (waf.ippubliks && waf.ippubliks.length > 0) {
                waf.ippubliks.forEach((ipPublik, ipIdx) => {
                  const ipLabel = ipPublik.ip_publik || 'IP Publik'; // Label menampilkan IP address
                  const ipNodeWidth = Math.max(minNodeWidth, getTextWidth(ipLabel) + 32);
                  
                  nodes.push({
                    id: `ip-${ipPublik.id}-waf-${waf.id}-deployment-${deployment.id}`,
                    label: ipLabel,
                    type: 'ipPublik',
                    data: ipPublik,
                    x: ipPublikColumnX,
                    y: deploymentY + 2 * (nodeHeight + nodeGapY), // Posisi Y di bawah WAF
                    width: ipNodeWidth
                  });
                  links.push({ source: `waf-${waf.id}-deployment-${deployment.id}`, target: `ip-${ipPublik.id}-waf-${waf.id}-deployment-${deployment.id}` });
                  
                  // Inperva - posisi Y di bawah IP Publik
                  if (ipPublik.inpervas && ipPublik.inpervas.length > 0) {
                    ipPublik.inpervas.forEach((inperva, inpIdx) => {
                      const inpLabel = inperva.name || 'Inperva';
                      const inpNodeWidth = Math.max(minNodeWidth, getTextWidth(inpLabel) + 32);
                      
                      nodes.push({
                        id: `inperva-${inperva.id}-ip-${ipPublik.id}-waf-${waf.id}-deployment-${deployment.id}`,
                        label: inpLabel,
                        type: 'inperva',
                        data: inperva,
                        x: inpervaColumnX,
                        y: deploymentY + 3 * (nodeHeight + nodeGapY), // Posisi Y di bawah IP Publik
                        width: inpNodeWidth
                      });
                      links.push({ source: `ip-${ipPublik.id}-waf-${waf.id}-deployment-${deployment.id}`, target: `inperva-${inperva.id}-ip-${ipPublik.id}-waf-${waf.id}-deployment-${deployment.id}` });
                      
                      // DNS - posisi Y di bawah Inperva
                      if (inperva.dns && inperva.dns.length > 0) {
                        inperva.dns.forEach((dns, dnsIdx) => {
                          const dnsLabel = dns.name && dns.type ? `${dns.name} ${dns.type}` : dns.name || 'DNS'; // Label menampilkan nama dan type
                          const dnsNodeWidth = Math.max(minNodeWidth, getTextWidth(dnsLabel) + 32);
                          
                          nodes.push({
                            id: `dns-${dns.id}-inperva-${inperva.id}-ip-${ipPublik.id}-waf-${waf.id}-deployment-${deployment.id}`,
                            label: dnsLabel,
                            type: 'dns',
                            data: dns,
                            x: dnsColumnX,
                            y: deploymentY + 4 * (nodeHeight + nodeGapY), // Posisi Y di bawah Inperva
                            width: dnsNodeWidth
                          });
                          links.push({ source: `inperva-${inperva.id}-ip-${ipPublik.id}-waf-${waf.id}-deployment-${deployment.id}`, target: `dns-${dns.id}-inperva-${inperva.id}-ip-${ipPublik.id}-waf-${waf.id}-deployment-${deployment.id}` });
                        });
                      }
                    });
                  }
                });
              }
            });
          }
          
          deploymentY += nodeHeight + nodeGapY;
        }
      });
      currentY += projectChildRows * (nodeHeight + nodeGapY);
    } else {
      // Project tanpa deployment
      currentY += nodeHeight + nodeGapY;
    }
  });
  return { nodes, links, columnLayout };
}

function renderDiagram() {
  if (svg) svg.remove();
  const { nodes, links, columnLayout } = getDiagramData(props.app);
  if (!nodes.length) return;
  
  // Hitung lebar diagram dari node terjauh
  const maxX = Math.max(...nodes.map(n => n.x + (n.width || minNodeWidth)));
  const width = Math.max(window.innerWidth, maxX + margin.left + 100); // Minimal lebar layar
  const height = Math.max(window.innerHeight, nodes.length > 0 ? Math.max(...nodes.map(n => n.y)) + nodeHeight + margin.top + 100 : window.innerHeight);
  
  svg = d3.select(svgContainer.value)
    .append('svg')
    .attr('width', width)
    .attr('height', height)
    .attr('viewBox', `0 0 ${width} ${height}`)
    .attr('preserveAspectRatio', 'xMidYMid meet')
    .style('background', '#fff')
    .style('max-width', '100%')
    .style('height', 'auto');



  // --- Tambahkan Judul Kolom ---
  const titles = [
    { key: 'app', label: 'App' },
    { key: 'project', label: 'Project' },
    { key: 'deployment', label: 'Deployment' },
    { key: 'service', label: 'Service API Gates' } // Tambahkan judul untuk Service API Gates
    // Hapus judul untuk WAF, IP Publik, Inperva, DNS karena berada di kolom yang sama
  ];

  svg.selectAll('text.column-title')
    .data(titles)
    .enter()
    .append('text')
    .attr('class', 'column-title')
    .attr('x', d => {
      const col = columnLayout[d.key];
      return col.x + col.width / 2; // Center the text in the column
    })
    .attr('y', margin.top / 1.5) // Position above the nodes
    .attr('text-anchor', 'middle')
    .attr('font-size', '16px')
    .attr('font-weight', '700')
    .attr('fill', '#1f2937')
    .style('display', d => nodes.some(n => n.type === d.key) ? null : 'none')
    .text(d => d.label);


  // --- Tambahkan garis vertikal pembatas kolom ---
  const linePositions = [];
  const columnKeys = ['app', 'project', 'deployment', 'service']; // 4 kolom sekarang
  columnKeys.forEach(key => {
    const nextKey = key === 'app' ? 'project' : 
                   key === 'project' ? 'deployment' : 
                   key === 'deployment' ? 'service' : 'service'; // Service adalah kolom terakhir
    // Hanya tampilkan garis jika ada node di kolom saat ini DAN kolom berikutnya
    if (nodes.some(n => n.type === key) && nodes.some(n => n.type === nextKey)) {
      const col = columnLayout[key];
      linePositions.push(col.x + col.width + nodeGapX / 2);
    }
  });


  linePositions.forEach(x => {
    svg.append('line')
      .attr('x1', x)
      .attr('x2', x)
      .attr('y1', margin.top / 2)
      .attr('y2', height - margin.top / 2)
      .attr('stroke', '#6b7280')
      .attr('stroke-width', 2)
      .attr('stroke-dasharray', '8,8')
      .attr('opacity', 0.8);
  });

  // Draw links
  svg.selectAll('line.link')
    .data(links)
    .enter()
    .append('line')
    .attr('class', 'link')
    .attr('x1', d => {
      const n = nodes.find(n => n.id === d.source);
      const targetNode = nodes.find(n => n.id === d.target);
      
      // Jika target adalah node infrastruktur (WAF, IP Publik, Inperva, DNS), gunakan garis vertikal
      if (targetNode.type === 'waf' || targetNode.type === 'ipPublik' || targetNode.type === 'inperva' || targetNode.type === 'dns') {
        return n.x + (n.width || minNodeWidth) / 2; // Mulai dari tengah node
      } else {
        return n.x + (n.width || minNodeWidth); // Mulai dari kanan node (garis horizontal)
      }
    })
    .attr('y1', d => {
      const n = nodes.find(n => n.id === d.source);
      const targetNode = nodes.find(n => n.id === d.target);
      
      // Jika target adalah node infrastruktur, gunakan garis vertikal
      if (targetNode.type === 'waf' || targetNode.type === 'ipPublik' || targetNode.type === 'inperva' || targetNode.type === 'dns') {
        return n.y + nodeHeight; // Mulai dari bawah node
      } else {
        return n.y + nodeHeight / 2; // Mulai dari tengah node (garis horizontal)
      }
    })
    .attr('x2', d => {
      const n = nodes.find(n => n.id === d.target);
      const targetNode = nodes.find(n => n.id === d.target);
      
      // Jika target adalah node infrastruktur, gunakan garis vertikal
      if (targetNode.type === 'waf' || targetNode.type === 'ipPublik' || targetNode.type === 'inperva' || targetNode.type === 'dns') {
        return n.x + (n.width || minNodeWidth) / 2; // Berakhir di tengah node
      } else {
        return n.x; // Berakhir di kiri node (garis horizontal)
      }
    })
    .attr('y2', d => {
      const n = nodes.find(n => n.id === d.target);
      const targetNode = nodes.find(n => n.id === d.target);
      
      // Jika target adalah node infrastruktur, gunakan garis vertikal
      if (targetNode.type === 'waf' || targetNode.type === 'ipPublik' || targetNode.type === 'inperva' || targetNode.type === 'dns') {
        return n.y; // Berakhir di atas node
      } else {
        return n.y + nodeHeight / 2; // Berakhir di tengah node (garis horizontal)
      }
    })
    .attr('stroke', '#374151')
    .attr('stroke-width', 3)
    .attr('marker-end', 'url(#arrow)')
    .attr('opacity', 0.8);

  // Arrow marker
  svg.append('defs').append('marker')
    .attr('id', 'arrow')
    .attr('viewBox', '0 0 10 10')
    .attr('refX', 10)
    .attr('refY', 5)
    .attr('markerWidth', 8)
    .attr('markerHeight', 8)
    .attr('orient', 'auto')
    .append('path')
    .attr('d', 'M 0 0 L 10 5 L 0 10 z')
    .attr('fill', '#374151');

  // Draw nodes
  const nodeGroup = svg.selectAll('g.node')
    .data(nodes)
    .enter()
    .append('g')
    .attr('class', 'node')
    .attr('transform', d => `translate(${d.x},${d.y})`)
    .style('cursor', 'pointer')
    .on('click', (event, d) => emit('node-click', d));

  // Function to determine node fill color based on label content
  function getNodeFillColor(label, type) {
    if (!label) return '#fff';
    
    // Warna berdasarkan tipe node
    if (type === 'deployment') {
      return '#FFE4B5FF'; // Orange untuk Deployment
    } else if (type === 'service') {
      return '#DDA0DDFF'; // Plum untuk Service
    } else if (type === 'waf') {
      return '#FFB6C1FF'; // Light Pink untuk WAF
    } else if (type === 'ipPublik') {
      return '#87CEEBFF'; // Sky Blue untuk IP Publik
    } else if (type === 'inperva') {
      return '#98FB98FF'; // Pale Green untuk Inperva
    } else if (type === 'dns') {
      return '#DDA0DDFF'; // Plum untuk DNS
    }
    
    // Warna berdasarkan label untuk node lama
    const lowerLabel = label.toLowerCase();
    if (lowerLabel.includes('dws')) {
      return '#AECDFFFF'; // Blue for DWS
    } else if (lowerLabel.includes('backend')) {
      return '#FF9A9AFF'; // Red for Backend
    } else if (lowerLabel.includes('layanan frontend')) {
      return '#9CFFC0FF'; // Green for Layanan Frontend
    }
    return '#fff'; // Default white
  }

  // Build a map from project node id to its color
  const projectColorMap = {};
  nodes.forEach(node => {
    if (node.type === 'project') {
      projectColorMap[node.id] = getNodeFillColor(node.label, node.type);
    }
  });

  // Build a map from deployment-service node id to project color
  const deploymentServiceColorMap = {};
  links.forEach(link => {
    if (link.source.startsWith('project-') && link.target.startsWith('deployment-')) {
      deploymentServiceColorMap[link.target] = projectColorMap[link.source];
    }
  });

  // Build a map from service node id to deployment-service color
  const serviceColorMap = {};
  links.forEach(link => {
    if (link.source.startsWith('deployment-') && link.target.startsWith('service-')) {
      serviceColorMap[link.target] = deploymentServiceColorMap[link.source] || '#fff';
    }
  });

  // Build a map from WAF node id to service color
  const wafColorMap = {};
  links.forEach(link => {
    if (link.source.startsWith('service-') && link.target.startsWith('waf-')) {
      wafColorMap[link.target] = serviceColorMap[link.source] || '#fff';
    } else if (link.source.startsWith('deployment-') && link.target.startsWith('waf-')) {
      wafColorMap[link.target] = deploymentServiceColorMap[link.source] || '#fff';
    }
  });

  // Build a map from IP Publik node id to WAF color
  const ipPublikColorMap = {};
  links.forEach(link => {
    if (link.source.startsWith('waf-') && link.target.startsWith('ip-')) {
      ipPublikColorMap[link.target] = wafColorMap[link.source] || '#fff';
    }
  });

  // Build a map from Inperva node id to IP Publik color
  const inpervaColorMap = {};
  links.forEach(link => {
    if (link.source.startsWith('ip-') && link.target.startsWith('inperva-')) {
      inpervaColorMap[link.target] = ipPublikColorMap[link.source] || '#fff';
    }
  });

  // Build a map from DNS node id to Inperva color
  const dnsColorMap = {};
  links.forEach(link => {
    if (link.source.startsWith('inperva-') && link.target.startsWith('dns-')) {
      dnsColorMap[link.target] = inpervaColorMap[link.source] || '#fff';
    }
  });

  nodeGroup.append('rect')
    .attr('width', d => d.width || minNodeWidth)
    .attr('height', nodeHeight)
    .attr('rx', 12)
    .attr('fill', d => {
      if (d.type === 'deployment') {
        return deploymentServiceColorMap[d.id] || getNodeFillColor(d.label, d.type);
      } else if (d.type === 'service') {
        return serviceColorMap[d.id] || getNodeFillColor(d.label, d.type);
      } else if (d.type === 'waf') {
        return wafColorMap[d.id] || getNodeFillColor(d.label, d.type);
      } else if (d.type === 'ipPublik') {
        return ipPublikColorMap[d.id] || getNodeFillColor(d.label, d.type);
      } else if (d.type === 'inperva') {
        return inpervaColorMap[d.id] || getNodeFillColor(d.label, d.type);
      } else if (d.type === 'dns') {
        return dnsColorMap[d.id] || getNodeFillColor(d.label, d.type);
      }
      return getNodeFillColor(d.label, d.type);
    }) // Fill color based on label or inherited color
    .attr('stroke', '#6b7280') // Default stroke
    .attr('stroke-width', 3)
    .on('mouseover', function(event, d) {
      d3.select(this)
        .attr('fill', '#FFFFFFFF')   // Hover fill
        .attr('stroke', '#000000FF'); // Hover stroke
    })
    .on('mouseout', function(event, d) {
      d3.select(this)
        .attr('fill', d => {
          if (d.type === 'deployment') {
            return deploymentServiceColorMap[d.id] || getNodeFillColor(d.label, d.type);
          } else if (d.type === 'service') {
            return serviceColorMap[d.id] || getNodeFillColor(d.label, d.type);
          } else if (d.type === 'waf') {
            return wafColorMap[d.id] || getNodeFillColor(d.label, d.type);
          } else if (d.type === 'ipPublik') {
            return ipPublikColorMap[d.id] || getNodeFillColor(d.label, d.type);
          } else if (d.type === 'inperva') {
            return inpervaColorMap[d.id] || getNodeFillColor(d.label, d.type);
          } else if (d.type === 'dns') {
            return dnsColorMap[d.id] || getNodeFillColor(d.label, d.type);
          }
          return getNodeFillColor(d.label, d.type);
        }) // Restore fill color
        .attr('stroke', '#a1a1aa'); // Kembali ke abu-abu
    });

  nodeGroup.append('text')
    .attr('x', d => (d.width || minNodeWidth) / 2)
    .attr('y', nodeHeight / 2 + 6)
    .attr('text-anchor', 'middle')
    .attr('font-size', 16)
    .attr('font-family', 'inherit')
    .attr('fill', '#18181b')
    .text(d => d.label)
    .style('pointer-events', 'none');

  // Tambahkan teks tahap di bawah node deployment
  nodeGroup.filter(d => d.type === 'deployment').each(function(d) {
    // Cek apakah deployment punya tahap
    if (d.data && d.data.tahap) {
      d3.select(this)
        .append('text')
        .attr('x', (d.width || minNodeWidth) / 2)
        .attr('y', nodeHeight + 18)
        .attr('text-anchor', 'middle')
        .attr('font-size', 13)
        .attr('fill', '#52525b')
        .text(`${d.data.tahap}`)
        .style('pointer-events', 'none');
    }
  });

  // Tambahkan teks API Gate di bawah node service
  nodeGroup.filter(d => d.type === 'service').each(function(d) {
    const apiGate = d.data && d.data.api_gate;
    if (apiGate) {
      d3.select(this)
        .append('text')
        .attr('x', (d.width || minNodeWidth) / 2)
        .attr('y', nodeHeight + 18)
        .attr('text-anchor', 'middle')
        .attr('font-size', 13)
        .attr('fill', '#52525b')
        .text(`API Gate ${apiGate}`)
        .style('pointer-events', 'none');
    }
  });

  // Tambahkan teks WAF di bawah node WAF
  nodeGroup.filter(d => d.type === 'waf').each(function(d) {
    d3.select(this)
      .append('text')
      .attr('x', (d.width || minNodeWidth) / 2)
      .attr('y', nodeHeight + 18)
      .attr('text-anchor', 'middle')
      .attr('font-size', 13)
      .attr('fill', '#52525b')
      .text('WAF') // Teks di bawah node menampilkan "WAF"
      .style('pointer-events', 'none');
  });

  // Tambahkan teks IP Publik di bawah node IP Publik
  nodeGroup.filter(d => d.type === 'ipPublik').each(function(d) {
    d3.select(this)
      .append('text')
      .attr('x', (d.width || minNodeWidth) / 2)
      .attr('y', nodeHeight + 18)
      .attr('text-anchor', 'middle')
      .attr('font-size', 13)
      .attr('fill', '#52525b')
      .text('IP Publik') // Teks di bawah node menampilkan "IP Publik"
      .style('pointer-events', 'none');
  });

  // Tambahkan teks Inperva di bawah node Inperva
  nodeGroup.filter(d => d.type === 'inperva').each(function(d) {
    d3.select(this)
      .append('text')
      .attr('x', (d.width || minNodeWidth) / 2)
      .attr('y', nodeHeight + 18)
      .attr('text-anchor', 'middle')
      .attr('font-size', 13)
      .attr('fill', '#52525b')
      .text('Inperva') // Teks di bawah node menampilkan "Inperva"
      .style('pointer-events', 'none');
  });

  // Tambahkan teks Name di bawah node Inperva
  nodeGroup.filter(d => d.type === 'inperva').each(function(d) {
    // Teks sudah diganti dengan "Inperva" di atas
  });

  // Tambahkan teks Name dan Type di bawah node DNS
  nodeGroup.filter(d => d.type === 'dns').each(function(d) {
    // Teks sudah diganti dengan "DNS" di atas
  });

  // Tambahkan teks DNS di bawah node DNS
  nodeGroup.filter(d => d.type === 'dns').each(function(d) {
    d3.select(this)
      .append('text')
      .attr('x', (d.width || minNodeWidth) / 2)
      .attr('y', nodeHeight + 18)
      .attr('text-anchor', 'middle')
      .attr('font-size', 13)
      .attr('fill', '#52525b')
      .text('DNS') // Teks di bawah node menampilkan "DNS"
      .style('pointer-events', 'none');
  });
}

onMounted(renderDiagram)
watch(() => props.app, renderDiagram)
</script>

<style scoped>
/* Component styles */
</style> 