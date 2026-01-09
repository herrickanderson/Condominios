<template>
    <apexchart type="bar" height="350" :options="chartOptions" :series="series" />
  </template>

  <script setup>
  import { ref, computed, watch } from 'vue';
  import ApexChart from 'vue3-apexcharts';

  const props = defineProps({
    historicalData: {
      type: Array,
      default: () => [],
    },
  });

  // Obtener períodos únicos (ordenados) de la data histórica
  const periods = computed(() => {
    const set = new Set();
    props.historicalData.forEach(item => set.add(item.periodo));
    return Array.from(set).sort();
  });

  // Agrupar la data por servicio y construir series para cada servicio
  const series = computed(() => {
    const groups = {};
    props.historicalData.forEach(item => {
      if (!groups[item.servicio]) {
        groups[item.servicio] = {};
      }
      groups[item.servicio][item.periodo] = item.total;
    });
    return Object.keys(groups).map(servicio => ({
      name: servicio,
      data: periods.value.map(p => groups[servicio][p] || 0)
    }));
  });

  const chartOptions = ref({
    chart: {
      id: 'historical-gastos',
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '55%',
      },
    },
    xaxis: {
      categories: periods.value,
    },
    title: {
      text: 'Histórico de Gastos por Servicio',
      align: 'left',
    },
    yaxis: {
      labels: {
        formatter: (val) => val.toFixed(2),
      },
    },
  });

  // Actualizar las categorías si cambian los períodos
  watch(periods, (newPeriods) => {
    chartOptions.value.xaxis.categories = newPeriods;
  });
  </script>
