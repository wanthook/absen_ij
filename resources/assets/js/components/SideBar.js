import React, { Component } from 'react'

export default class SideBar extends Component {
    render() {
        return (
            <div>
                {/* Left side column. contains the logo and sidebar */}
                <aside className="main-sidebar">
                {/* sidebar: style can be found in sidebar.less */}
                <section className="sidebar">
                    {/* Sidebar user panel */}
                    <div className="user-panel">
                    <div className="pull-left image">
                        <img src="dist/img/user2-160x160.jpg" className="img-circle" alt="User Gambar" />
                    </div>
                    <div className="pull-left info">
                        <p>Alexander Pierce</p>
                        <a href="nothing"><i className="fa fa-circle text-success" /> Online</a>
                    </div>
                    </div>
                    
                    {/* sidebar menu: : style can be found in sidebar.less */}
                    <ul className="sidebar-menu" data-widget="tree">
                    </ul>
                </section>
                {/* /.sidebar */}
                </aside>

            </div>
        )
    }
}
