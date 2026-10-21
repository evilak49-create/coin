import { defineStore } from "pinia";
import { UserState } from "@/stores/interface";
import piniaPersistConfig from "@/stores/helper/persist";
import { getUserInfoApi } from "@/api/modules/login";

export const useUserStore = defineStore({
  id: "geeker-user",
  state: (): UserState => ({
    token: "",
    userInfo: null
  }),
  getters: {},
  actions: {
    // Set Token
    setToken(token: string) {
      this.token = token;
    },
    // Set setUserInfo
    setUserInfo(userInfo: UserState["userInfo"]) {
      this.userInfo = userInfo;
    },
    // Get UserInfo - 获取用户信息并存储所有字段
    async getUserInfo() {
      const { data } = await getUserInfoApi();
      this.userInfo = data;
      return data;
    }
  },
  persist: piniaPersistConfig("geeker-user")
});
