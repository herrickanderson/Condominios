<template>
    <!-- Gráfico de barras usando ApexCharts -->
    <apexchart type="bar" height="300" :options="chartOptions" :series="series" />
  </template>

  <script setup>
  import { ref, computed, watch } from 'vue';
  import ApexChart from 'vue3-apexcharts';

  const props = defineProps({
    gastosPorMes: {
      type: Array,
      default: () => [],
    },
  });

  // Inicializa la serie con los datos recibidos
  const series = ref([
    {
      name: 'Gastos',
      data: props.gastosPorMes.map(item => item.total),
    },
  ]);

  // Lista extendida de colores
  const defaultColors = [
    '#FF4560', '#008FFB', '#00E396', '#FEB019',
    '#775DD0', '#3F51B5', '#FF9F1C', '#2EC4B6',
    '#FF595E', '#8AC926'
  ];

  // Computamos los colores para cada barra
  const colors = computed(() => {
    return props.gastosPorMes.map((_, index) => defaultColors[index % defaultColors.length]);
  });

  const chartOptions = ref({
    chart: {
      id: 'chart-gastos-mes',
    },
    plotOptions: {
      bar: {
        distributed: true,
        columnWidth: '50%',
      },
    },
    xaxis: {
      categories: props.gastosPorMes.map(item => item.mes),
    },
    title: {
      text: 'Gastos totales por Mes',
      align: 'left',
    },
    yaxis: {
      labels: {
        formatter: (val) => val.toFixed(2),
      },
    },
    colors: colors.value,
  });

  // Actualizar la serie, categorías y colores si cambian los datos
  watch(
    () => props.gastosPorMes,
    () => {
      series.value = [
        {
          name: 'Gastos',
          data: props.gastosPorMes.map(item => item.total),
        },
      ];
      chartOptions.value.xaxis.categories = props.gastosPorMes.map(item => item.mes);
      chartOptions.value.colors = colors.value;
    },
    { deep: true }
  );
  </script>
