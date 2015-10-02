package com.compreingressos.knowledge.controller;

import com.compreingressos.knowledge.bean.FuncaoSistemaFacade;
import com.compreingressos.knowledge.bean.UsuarioFacade;
import com.compreingressos.knowledge.model.FuncaoSistema;
import com.compreingressos.knowledge.model.Usuario;
import java.io.Serializable;
import java.util.List;
import javax.ejb.EJB;
import javax.faces.application.FacesMessage;
import javax.faces.bean.ManagedBean;
import javax.faces.bean.SessionScoped;
import javax.faces.context.FacesContext;
import javax.servlet.http.HttpSession;
import org.primefaces.model.menu.DefaultMenuItem;
import org.primefaces.model.menu.DefaultMenuModel;
import org.primefaces.model.menu.DefaultSubMenu;
import org.primefaces.model.menu.MenuModel;

/**
 *
 * @author edicarlos.barbosa
 */
@ManagedBean(name = "loginController")
@SessionScoped
public class LoginController implements Serializable {

    @EJB
    private UsuarioFacade ejbUsuario;
    @EJB
    private FuncaoSistemaFacade ejbFacadeFuncaoSistema;
    public Usuario usuario;
    private MenuModel model;

    public LoginController() {
        usuario = new Usuario();
    }

    public Usuario getUsuario() {
        return usuario;
    }

    public void setUsuario(Usuario usuario) {
        this.usuario = usuario;
    }

    public UsuarioFacade getFacade() {
        return ejbUsuario;
    }

    public FuncaoSistemaFacade getFacadeFuncaoSistema() {
        return ejbFacadeFuncaoSistema;
    }

    public String login() {
        Usuario usuarioBanco = getFacade().login(usuario);
        if (usuarioBanco != null) {
            usuario = usuarioBanco;
            usuario.setLogado(true);
            HttpSession session = (HttpSession) FacesContext.getCurrentInstance().getExternalContext().getSession(false);
            session.setAttribute("autenticado", true);
            return "pages/Tarefas";
        } else {
            usuario.setLogado(false);
            FacesMessage mensagem = new FacesMessage("Login ou senha inválidos!");
            FacesContext.getCurrentInstance().addMessage("mensagem", mensagem);
            return "index";
        }
    }

    public String logout() {
        if (usuario.isLogado()) {
            usuario = new Usuario();
            usuario.setLogado(false);
            HttpSession session = (HttpSession) FacesContext.getCurrentInstance().getExternalContext().getSession(false);
            session.removeAttribute("autenticado");
        }
        return "/index";
    }

    public MenuModel getModel() {
        model = new DefaultMenuModel();
        List<FuncaoSistema> funcaoSistemaLista = getFacadeFuncaoSistema().findPai(usuario.getId());
        for (FuncaoSistema funcao : funcaoSistemaLista) {
            if (funcao.getUrl().isEmpty()) {
                DefaultSubMenu subMenu = new DefaultSubMenu(funcao.getDescricao());
                for (FuncaoSistema subFuncao : funcaoSistemaLista) {
                    if (subFuncao.getFuncaoSistema() != null && subFuncao.getFuncaoSistema().getId().equals(funcao.getId())) {
                        DefaultMenuItem item = new DefaultMenuItem();
                        item.setValue(subFuncao.getDescricao());
                        item.setOutcome(subFuncao.getUrl());
                        subMenu.addElement(item);
                    }
                }
                model.addElement(subMenu);
            } else if (funcao.getFuncaoSistema() == null) {
                DefaultMenuItem menuItem = new DefaultMenuItem();
                menuItem.setValue(funcao.getDescricao());
                menuItem.setOutcome(funcao.getUrl());
                model.addElement(menuItem);
            }
        }
        model.generateUniqueIds();
        return model;
    }

}
