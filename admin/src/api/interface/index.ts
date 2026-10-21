// 请求响应参数（不包含data）
export interface Result {
  code: string;
  msg: string;
}

// 请求响应参数（包含data）
export interface ResultData<T = any> extends Result {
  data: T;
}

// 分页响应参数
export interface ResPage<T> {
  list: T[];
  page: number;
  limit: number;
  total: number;
}

// 分页请求参数
export interface ReqPage {
  page: number;
  limit: number;
}

// 文件上传模块
export namespace Upload {
  export interface ResFileUrl {
    fileUrl: string;
  }
}

// 登录模块
export namespace Login {
  export interface ReqLoginForm {
    username: string;
    password: string;
    captcha_code?: string;
    captcha_key?: string;
  }
  export interface ResLogin {
    access_token: string;
    token_type: string;
    expires_in: number;
  }
  export interface ResAuthButtons {
    [key: string]: string[];
  }
  export interface ResCaptcha {
    captcha_key: string;
    image: string;
  }
  export interface ResUserInfo {
    id: number;
    username: string;
    status: number;
    ip_last: string;
    login_time_last: string;
    created_at: string;
    role: Array<{
      id: number;
      name: string;
      slug: string;
    }>;
  }
}

// 用户管理模块
export namespace User {
  export interface ReqUserParams extends ReqPage {
    username?: string;
  }

  export interface ResUserRole {
    id: number;
    name: string;
    slug: string;
  }

  export interface ResUserList {
    id: number;
    username: string;
    status: number;
    ip_last?: string | null;
    login_time_last?: string | null;
    created_at?: string;
    role?: ResUserRole[];
    role_ids?: number[];
  }
}
