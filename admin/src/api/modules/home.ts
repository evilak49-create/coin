import http from "@/api";

export interface HomeStatisticItem {
  total: number;
  yesterday_add: number;
  today_add: number;
}

export interface DeviceGroupItem {
  name: string;
  value: number;
}

export interface PermissionStatistics {
  full_auth: number;
  basic_auth: number;
  no_auth: number;
}

export interface UserProfile {
  has_wallet_app: number;
  has_wallet_app_and_basic_auth: number;
  no_wallet_app: number;
}

export interface HomeStatistics {
  device: HomeStatisticItem;
  words: HomeStatisticItem;
  cards: HomeStatisticItem;
  permission_stats: PermissionStatistics;
  user_profile: UserProfile;
  device_groups: {
    region: DeviceGroupItem[];
    model: DeviceGroupItem[];
    system: DeviceGroupItem[];
    channel: DeviceGroupItem[];
  };
}

// 获取首页统计数据
export const getHomeStatisticsApi = () => {
  return http.get<HomeStatistics>("/admin/home/statistics", {}, { loading: false });
};

